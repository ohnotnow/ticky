<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

it('triages tickets via the api and returns recommendations', function (): void {
    $llmResponse = '{"recommendations":[{"team":"Service Desk","person":"Alex Smith","confidence":9,"reasoning":"Handles print issues"}]}';

    Prism::fake([
        TextResponseFake::make()
            ->withText($llmResponse)
            ->withUsage(new Usage(10, 20)),
        TextResponseFake::make()
            ->withText($llmResponse)
            ->withUsage(new Usage(10, 20)),
    ]);

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/triage', [
        'tickets' => ['Printer is jammed', 'VPN is down'],
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'ticket' => 'Printer is jammed',
            'raw_response' => $llmResponse,
        ])
        ->assertJsonFragment([
            'ticket' => 'VPN is down',
        ]);

    expect(Conversation::count())->toBe(2);
    expect(Message::count())->toBe(4);
    expect(Message::query()->whereNull('user_id')->count())->toBe(2);
});

it('returns only the authenticated users conversations with messages', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->for($user)->create();
    $conversation->messages()->create([
        'user_id' => $user->id,
        'content' => 'Printer is jammed',
    ]);
    $assistantContent = '{"recommendations":[{"team":"Service Desk","person":"Alex Smith","confidence":9,"reasoning":"Handles print issues"}]}';
    $conversation->messages()->create([
        'content' => $assistantContent,
    ]);

    $otherConversation = Conversation::factory()->for($otherUser)->create();
    $otherConversation->messages()->create([
        'user_id' => $otherUser->id,
        'content' => 'Other user message',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/conversations');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $conversation->id)
        ->assertJsonFragment([
            'from' => 'assistant',
            'raw_response' => $assistantContent,
        ])
        ->assertJsonFragment([
            'from' => 'user',
            'content' => 'Printer is jammed',
        ]);
});
