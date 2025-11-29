<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\LlmService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class TriageChat extends Component
{
    /**
     * Holds the results for each ticket in the current submission.
     */
    public array $ticketRuns = [];

    public string $prompt = '';

    protected LlmService $llmService;

    public function boot(LlmService $llmService): void
    {
        $this->llmService = $llmService;
    }

    public function send(): void
    {
        $this->validate([
            'prompt' => ['required', 'string'],
        ]);

        $tickets = $this->extractTickets($this->prompt);

        $this->ticketRuns = [];

        foreach ($tickets as $ticket) {
            $this->ticketRuns[] = $this->processTicket($ticket);
        }

        $this->prompt = '';
    }

    protected function extractTickets(string $prompt): array
    {
        return collect(preg_split('/^---$/m', $prompt))
            ->map(fn (string $ticket): string => trim($ticket))
            ->filter(fn (string $ticket): bool => $ticket !== '')
            ->values()
            ->all();
    }

    protected function processTicket(string $ticket): array
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
        ]);

        $conversation->messages()->create([
            'user_id' => Auth::id(),
            'content' => $ticket,
        ]);

        $messages = $conversation
            ->messages()
            ->oldest()
            ->get();

        $response = $this->llmService->generateResponse($conversation, $messages);

        $conversation->messages()->create([
            'content' => $response,
        ]);

        return [
            'conversation_id' => $conversation->id,
            'prompt' => $ticket,
            'response' => $response,
            'recommendations' => Message::recommendationsFromContent($response),
        ];
    }

    public function render(): View
    {
        return view('livewire.triage-chat')->layout('components.layouts.app');
    }
}
