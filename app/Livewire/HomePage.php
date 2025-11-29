<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HomePage extends Component
{
    /** @var Collection<int, Conversation> */
    public Collection $conversations;

    public function mount(): void
    {
        $this->conversations = Conversation::query()
            ->with('messages')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.home-page')->layout('components.layouts.app');
    }
}
