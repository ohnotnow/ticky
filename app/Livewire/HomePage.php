<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class HomePage extends Component
{
    use WithPagination;

    public int $perPage = 50;

    public int $page = 1;

    public function render(): View
    {
        $conversations = Conversation::query()
            ->with(['messages' => fn ($query) => $query->oldest()->limit(1)])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.home-page', [
            'conversations' => $conversations,
        ])->layout('components.layouts.app');
    }
}
