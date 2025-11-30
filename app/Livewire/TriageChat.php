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

    public bool $showModelPicker = false;

    public ?string $selectedModel = null;

    protected LlmService $llmService;

    public function mount(): void
    {
        $this->selectedModel = config('ticky.llm_model');
    }

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
        $providerModel = $this->selectedProviderModel();

        $this->ticketRuns = [];

        foreach ($tickets as $ticket) {
            $this->ticketRuns[] = $this->processTicket($ticket, $providerModel);
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

    protected function processTicket(string $ticket, ?string $providerModel): array
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
        ]);

        $conversation->messages()->create([
            'user_id' => Auth::id(),
            'content' => $ticket,
        ]);

        $conversation->load(['messages' => fn ($query) => $query->oldest()]);

        $response = $this->llmService->generateResponse($conversation, providerModel: $providerModel);

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

    protected function selectedProviderModel(): ?string
    {
        if ($this->selectedModel === null) {
            return null;
        }

        foreach ($this->modelChoices as $provider => $models) {
            foreach (array_keys($models) as $model) {
                $candidate = "{$provider}/{$model}";

                if ($candidate === $this->selectedModel) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    public function getModelChoicesProperty(): array
    {
        return config('ticky.model_choices', []);
    }

    public function openModelPicker(): void
    {
        $this->showModelPicker = true;
    }

    public function clearModelSelection(): void
    {
        $this->selectedModel = config('ticky.llm_model');
        $this->showModelPicker = false;
    }

    public function updatedSelectedModel(): void
    {
        $this->showModelPicker = false;
    }

    public function render(): View
    {
        return view('livewire.triage-chat')->layout('components.layouts.app');
    }

    public function getSelectedModelLabelProperty(): string
    {
        $selected = $this->selectedProviderModel() ?? config('ticky.llm_model');

        [$provider, $model] = explode('/', $selected, 2);

        $label = $this->modelChoices[$provider][$model]['label'] ?? $model;

        return "{$label} (".ucfirst($provider).')';
    }
}
