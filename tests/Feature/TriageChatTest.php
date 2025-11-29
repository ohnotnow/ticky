<?php

use App\Livewire\TriageChat;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

it('sends a prompt and shows the llm response', function (): void {
    $llmResponse = '{"recommendations":[{"team":"College Infrastructure","person":"Fiona Drummond","confidence":9,"reasoning":"Handles Linux servers."},{"team":"Research Computing","person":"Hamish Baxter","confidence":7,"reasoning":"Focuses on research software."}]}';

    Prism::fake([
        TextResponseFake::make()
            ->withText($llmResponse)
            ->withUsage(new Usage(10, 20)),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(TriageChat::class)
        ->set('prompt', 'The GPU node is failing jobs')
        ->call('send')
        ->assertSet('response', $llmResponse)
        ->assertSee('College Infrastructure')
        ->assertSee('Primary Recommendation');

    expect(Conversation::count())->toBe(1);
    expect(Message::count())->toBe(2);

    $assistantMessage = Message::query()->whereNull('user_id')->first();

    expect($assistantMessage?->content)->toBe($llmResponse);
});
