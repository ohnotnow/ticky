<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Services\LlmService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomePage extends Component
{
    use WithPagination;

    protected LlmService $llmService;

    public int $perPage = 50;

    public int $page = 1;

    public string $filter = '';

    public bool $showAll = false;

    public bool $showConversation = false;

    public ?Conversation $activeConversation = null;

    /** @var array<int, array<string, mixed>> */
    public array $activeMessages = [];

    public function boot(LlmService $llmService): void
    {
        $this->llmService = $llmService;
    }

    public function openConversation(int $conversationId): void
    {
        $conversation = Conversation::query()
            ->with(['messages' => fn ($query) => $query->oldest()])
            ->when(! $this->showAll, fn ($query) => $query->where('user_id', Auth::id()))
            ->find($conversationId);

        if (! $conversation || (! $this->showAll && $conversation->user_id !== Auth::id())) {
            throw new NotFoundHttpException;
        }

        $this->activeConversation = $conversation;
        $this->activeMessages = $conversation->messages
            ->map(fn ($message) => [
                'from' => $message->isFromUser() ? 'User' : 'Assistant',
                'content' => $message->content,
                'model' => $message->model,
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

    public function retryConversation(int $conversationId): void
    {
        $conversation = Conversation::query()
            ->when(! $this->showAll, fn ($query) => $query->where('user_id', Auth::id()))
            ->find($conversationId);

        if (! $conversation) {
            return;
        }

        // Remove existing assistant responses to start fresh
        $conversation->messages()->whereNull('user_id')->delete();

        // Refresh conversation to ensure only user messages are passed to LLM
        $conversation->refresh();
        $conversation->load('messages');

        $llmResponse = $this->llmService->generateResponse($conversation);

        $conversation->messages()->create([
            'content' => $llmResponse['text'],
            'model' => $llmResponse['model'],
        ]);

        $this->openConversation($conversation->id);
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatedShowAll(): void
    {
        $this->resetPage();
        $this->closeConversation();
    }

    public function render(): View
    {
        $conversations = Conversation::query()
            ->with(['messages' => fn ($query) => $query->oldest()->limit(1)])
            ->when(! $this->showAll, fn ($query) => $query->where('user_id', Auth::id()))
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
