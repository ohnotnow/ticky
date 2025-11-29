<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomePage extends Component
{
    use WithPagination;

    public int $perPage = 50;

    public int $page = 1;

    public string $filter = '';

    public bool $showConversation = false;

    public ?Conversation $activeConversation = null;

    /** @var array<int, array<string, mixed>> */
    public array $activeMessages = [];

    public function openConversation(int $conversationId): void
    {
        $conversation = Conversation::query()
            ->with(['messages' => fn ($query) => $query->oldest()])
            ->where('user_id', Auth::id())
            ->find($conversationId);

        if (! $conversation) {
            throw new NotFoundHttpException;
        }

        $this->activeConversation = $conversation;
        $this->activeMessages = $conversation->messages
            ->map(fn ($message) => [
                'from' => $message->isFromUser() ? 'You' : 'Assistant',
                'content' => $message->content,
                'at' => $message->created_at,
                'recommendations' => $message->recommendationsForView(),
            ])
            ->all();

        $this->showConversation = true;
    }

    public function closeConversation(): void
    {
        $this->reset(['showConversation', 'activeConversation', 'activeMessages']);
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $conversations = Conversation::query()
            ->with(['messages' => fn ($query) => $query->oldest()->limit(1)])
            ->where('user_id', Auth::id())
            ->when($this->filter, fn ($query) => $query->whereHas('messages', function ($messageQuery) {
                $messageQuery->where('content', 'like', '%'.$this->filter.'%');
            }))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.home-page', [
            'conversations' => $conversations,
        ])->layout('components.layouts.app');
    }
}
