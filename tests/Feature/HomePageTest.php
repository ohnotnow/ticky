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

    for ($i = 0; $i < 12; $i++) {
        $createdAt = Carbon::now()->subMinutes($i);

        $conversation = Conversation::factory()->create([
            'user_id' => $user->id,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

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
        ->assertDontSee('Ticket 0');

    Livewire::test(HomePage::class)
        ->set('perPage', 5)
        ->call('gotoPage', 3)
        ->assertSee('Ticket 11')
        ->assertDontSee('Ticket 0');
});
