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
        @include('partials.assistant-recommendations', ['recommendations' => $recommendations])
    @elseif ($response)
        <flux:card>
            <flux:heading size="md" level="2">LLM response</flux:heading>
            <pre class="mt-3 whitespace-pre-wrap font-mono text-sm">{{ $response }}</pre>
        </flux:card>
    @endif
</div>
