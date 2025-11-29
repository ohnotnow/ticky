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
    Prism::fake([
        TextResponseFake::make()
            ->withText('{"team":"Research Computing","person":"Rory Johnstone"}')
            ->withUsage(new Usage(10, 20)),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(TriageChat::class)
        ->set('prompt', 'The GPU node is failing jobs')
        ->call('send')
        ->assertSet('response', '{"team":"Research Computing","person":"Rory Johnstone"}')
        ->assertSee('Research Computing');

    expect(Conversation::count())->toBe(1);
    expect(Message::count())->toBe(2);

    $assistantMessage = Message::query()->whereNull('user_id')->first();

    expect($assistantMessage?->content)->toBe('{"team":"Research Computing","person":"Rory Johnstone"}');
});
