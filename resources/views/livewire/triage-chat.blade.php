<div class="space-y-6">
    <div class="space-y-2">
        <flux:heading size="xl" level="1">Ticket triage</flux:heading>
        <flux:text>Paste a support ticket and ask the assistant where it should go.</flux:text>
    </div>

    @error('prompt')
        <flux:callout variant="danger" icon="exclamation-triangle">{{ $message }}</flux:callout>
    @enderror

    <form wire:submit="send" class="space-y-4">
        <flux:composer
            wire:model.live="prompt"
            label="Prompt"
            label:sr-only
            placeholder="How can I help you today?"
        >
            <x-slot name="actionsLeading">
                <flux:button size="sm" variant="subtle" icon="paper-clip" />
                <flux:button size="sm" variant="subtle" icon="slash" />
                <flux:button size="sm" variant="subtle" icon="adjustments-horizontal" />
            </x-slot>

            <x-slot name="actionsTrailing">
                <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" class="cursor-pointer">
                    Send
                </flux:button>
            </x-slot>
        </flux:composer>
    </form>

    @if (count($recommendations))
        <div class="space-y-3">
            @foreach ($recommendations as $index => $recommendation)
                <flux:callout
                    :variant="$index === 0 ? 'success' : 'secondary'"
                    :icon="$index === 0 ? 'check-circle' : 'information-circle'"
                    :heading="$index === 0 ? 'Primary Recommendation' : 'Recommendation ' . ($index + 1)"
                >
                    <div class="space-y-1">
                        <flux:text class="font-semibold">Team: {{ $recommendation['team'] ?? 'Unknown' }}</flux:text>
                        <flux:text>Person: <b>{{ $recommendation['person'] ?? 'Unspecified' }}</b></flux:text>
                        <flux:text>Confidence: <b>{{ $recommendation['confidence'] ?? 'N/A' }}/10</b></flux:text>
                        <flux:text>{{ $recommendation['reasoning'] ?? 'No reasoning provided.' }}</flux:text>
                    </div>
                </flux:callout>
            @endforeach
        </div>
    @elseif ($response)
        <flux:card>
            <flux:heading size="md" level="2">LLM response</flux:heading>
            <pre class="mt-3 whitespace-pre-wrap font-mono text-sm">{{ $response }}</pre>
        </flux:card>
    @endif
</div>
