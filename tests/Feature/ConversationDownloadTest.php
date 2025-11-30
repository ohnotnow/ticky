<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('downloads recommendations as json for the owner', function (): void {
    $user = User::factory()->create();

    $conversation = Conversation::factory()->for($user)->create([
        'created_at' => Carbon::parse('2024-03-01 12:00:00'),
    ]);

    $conversation->messages()->create([
        'user_id' => $user->id,
        'content' => 'Printer is jammed',
    ]);

    $assistantContent = '{"recommendations":[{"team":"Service Desk","person":"Alex Smith","confidence":9,"reasoning":"Handles print issues"}]}';

    $conversation->messages()->create([
        'content' => $assistantContent,
        'created_at' => Carbon::parse('2024-03-01 12:05:00'),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('conversations.download.json', $conversation));

    $response->assertDownload('conversation-'.$conversation->id.'-recommendations.json')
        ->assertHeader('content-type', 'application/json');

    $payload = json_decode($response->streamedContent(), true);

    expect($payload)->toMatchArray([
        'conversation_id' => $conversation->id,
        'ticket' => 'Printer is jammed',
        'raw_response' => $assistantContent,
    ]);
    expect($payload['recommendations'][0]['team'])->toBe('Service Desk');
});

it('downloads recommendations as markdown', function (): void {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->for($user)->create();

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'VPN is down',
    ]);

    Message::factory()->assistant()->create([
        'conversation_id' => $conversation->id,
        'content' => '{"recommendations":[{"team":"Network Operations","person":"Jamie Lee","confidence":8,"reasoning":"Owns VPN infrastructure."}]}',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('conversations.download.markdown', $conversation));

    $response->assertDownload('conversation-'.$conversation->id.'-recommendations.md')
        ->assertHeader('content-type', 'text/markdown; charset=utf-8');

    $content = $response->streamedContent();

    expect($content)->toContain('Conversation #'.$conversation->id.' recommendations');
    expect($content)->toContain('VPN is down');
    expect($content)->toContain('Team:** Network Operations');
});

it('returns 404 when accessing another users conversation', function (): void {
    $conversation = Conversation::factory()->create();

    $otherUser = User::factory()->create();

    $this->actingAs($otherUser);

    $this->get(route('conversations.download.json', $conversation))
        ->assertNotFound();
});
