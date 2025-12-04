<?php

use App\Livewire\HomePage;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('paginates conversations and shows newest first', function (): void {
    $user = User::factory()->create();

    $conversationIds = [];

    for ($i = 0; $i < 12; $i++) {
        $createdAt = Carbon::now()->subMinutes($i);

        $conversation = Conversation::factory()->create([
            'user_id' => $user->id,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $conversationIds[] = $conversation->id;

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => "Ticket {$i}",
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    $this->actingAs($user);

    Livewire::test(HomePage::class)
        ->set('perPage', 5)
        ->assertSee('Ticky')
        ->assertSee('Triage ticket')
        ->assertSee('Ticket 0')
        ->assertDontSee('Ticket 11')
        ->assertViewHas('conversations', fn ($paginator) => $paginator->hasPages())
        ->call('gotoPage', 2)
        ->assertSee('Ticket 5')
        ->assertDontSee('Ticket 0')
        ->call('openConversation', $conversationIds[0])
        ->assertSet('showConversation', true)
        ->assertSee('Conversation #'.$conversationIds[0])
        ->assertSee('Ticket 0');

    Livewire::test(HomePage::class)
        ->set('perPage', 5)
        ->call('gotoPage', 3)
        ->assertSee('Ticket 11')
        ->assertDontSee('Ticket 0');
});

it('filters conversations by message content', function (): void {
    $user = User::factory()->create();

    $linuxConversation = Conversation::factory()->create([
        'user_id' => $user->id,
    ]);

    Message::factory()->create([
        'conversation_id' => $linuxConversation->id,
        'user_id' => $user->id,
        'content' => 'Linux server issue with MATLAB',
    ]);

    $otherConversation = Conversation::factory()->create([
        'user_id' => $user->id,
    ]);

    Message::factory()->create([
        'conversation_id' => $otherConversation->id,
        'user_id' => $user->id,
        'content' => 'Printer jam on level 2',
    ]);

    $this->actingAs($user);

    Livewire::test(HomePage::class)
        ->set('filter', 'linux')
        ->assertSee('Linux server issue')
        ->assertDontSee('Printer jam')
        ->assertViewHas('conversations', fn ($paginator) => $paginator->total() === 1);
});

it('can retry a conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'My printer is broken',
    ]);
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => json_encode(['recommendations' => []]),
        'model' => 'old-model',
    ]);

    $this->actingAs($user);

    $this->mock(App\Services\LlmService::class, function ($mock) {
        $mock->shouldReceive('generateResponse')
            ->once()
            ->andReturn(['text' => json_encode(['recommendations' => [['team' => 'New Team']]]), 'model' => 'new-model']);
    });

    Livewire::test(HomePage::class)
        ->call('retryConversation', $conversation->id)
        ->assertSet('showConversation', true);

    // Verify database state
    expect($conversation->fresh()->messages)->toHaveCount(2);
    expect($conversation->messages()->whereNull('user_id')->first()->model)->toBe('new-model');
});

it('can clear selection', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(HomePage::class)
        ->set('selectedConversationIds', [$conversation->id])
        ->assertSet('selectedConversationIds', [$conversation->id])
        ->call('clearSelection')
        ->assertSet('selectedConversationIds', []);
});

it('can bulk re-roll selected conversations', function () {
    $user = User::factory()->create();

    $conversation1 = Conversation::factory()->create(['user_id' => $user->id]);
    Message::factory()->create([
        'conversation_id' => $conversation1->id,
        'user_id' => $user->id,
        'content' => 'Printer issue',
    ]);
    Message::factory()->create([
        'conversation_id' => $conversation1->id,
        'user_id' => null,
        'content' => json_encode(['recommendations' => []]),
        'model' => 'old-model',
    ]);

    $conversation2 = Conversation::factory()->create(['user_id' => $user->id]);
    Message::factory()->create([
        'conversation_id' => $conversation2->id,
        'user_id' => $user->id,
        'content' => 'VPN issue',
    ]);
    Message::factory()->create([
        'conversation_id' => $conversation2->id,
        'user_id' => null,
        'content' => json_encode(['recommendations' => []]),
        'model' => 'old-model',
    ]);

    $llmResponse = '{"recommendations":[{"team":"New Team","person":"Someone","confidence":8,"reasoning":"Re-rolled"}]}';

    Prism\Prism\Facades\Prism::fake([
        Prism\Prism\Testing\TextResponseFake::make()->withText($llmResponse),
        Prism\Prism\Testing\TextResponseFake::make()->withText($llmResponse),
    ]);

    $this->actingAs($user);

    Livewire::test(HomePage::class)
        ->set('selectedConversationIds', [$conversation1->id, $conversation2->id])
        ->call('bulkRetryConversations')
        ->assertSet('selectedConversationIds', []);

    expect($conversation1->fresh()->messages)->toHaveCount(2);
    expect($conversation1->messages()->whereNull('user_id')->first()->content)->toBe($llmResponse);

    expect($conversation2->fresh()->messages)->toHaveCount(2);
    expect($conversation2->messages()->whereNull('user_id')->first()->content)->toBe($llmResponse);
});

it('cannot bulk re-roll other users conversations when showAll is false', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownConversation = Conversation::factory()->create(['user_id' => $user->id]);
    Message::factory()->create([
        'conversation_id' => $ownConversation->id,
        'user_id' => $user->id,
        'content' => 'My issue',
    ]);

    $otherConversation = Conversation::factory()->create(['user_id' => $otherUser->id]);
    Message::factory()->create([
        'conversation_id' => $otherConversation->id,
        'user_id' => $otherUser->id,
        'content' => 'Their issue',
    ]);
    Message::factory()->create([
        'conversation_id' => $otherConversation->id,
        'user_id' => null,
        'content' => json_encode(['recommendations' => []]),
        'model' => 'original-model',
    ]);

    $llmResponse = '{"recommendations":[{"team":"New Team","person":"Someone","confidence":8,"reasoning":"Re-rolled"}]}';

    Prism\Prism\Facades\Prism::fake([
        Prism\Prism\Testing\TextResponseFake::make()->withText($llmResponse),
    ]);

    $this->actingAs($user);

    Livewire::test(HomePage::class)
        ->set('selectedConversationIds', [$ownConversation->id, $otherConversation->id])
        ->call('bulkRetryConversations');

    // Own conversation should have new assistant message
    expect($ownConversation->fresh()->messages)->toHaveCount(2);
    expect($ownConversation->messages()->whereNull('user_id')->first()->content)->toBe($llmResponse);

    // Other user's conversation should remain unchanged
    expect($otherConversation->fresh()->messages()->whereNull('user_id')->first()->model)->toBe('original-model');
});
