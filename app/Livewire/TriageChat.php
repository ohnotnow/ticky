<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Services\LlmService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class TriageChat extends Component
{
    public Conversation $conversation;

    public string $prompt = '';

    public ?string $response = null;

    protected LlmService $llmService;

    public function boot(LlmService $llmService): void
    {
        $this->llmService = $llmService;
    }

    public function mount(): void
    {
        $this->conversation = Conversation::create([
            'user_id' => Auth::id(),
        ]);
    }

    public function send(): void
    {
        $this->validate([
            'prompt' => ['required', 'string'],
        ]);

        $this->conversation->messages()->create([
            'user_id' => Auth::id(),
            'content' => $this->prompt,
        ]);

        $messages = $this->conversation
            ->messages()
            ->oldest()
            ->get();

        $this->response = $this->llmService->generateResponse($this->conversation, $messages);

        $this->conversation->messages()->create([
            'content' => $this->response,
        ]);

        $this->prompt = '';
    }

    public function render(): View
    {
        return view('livewire.triage-chat')->layout('components.layouts.app');
    }
}
