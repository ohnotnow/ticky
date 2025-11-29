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
            placeholder="Paste one or more tickets. Separate multiple tickets with --- on its own line."
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

    @foreach ($ticketRuns as $ticketRun)
        <div wire:key="ticket-run-{{ $ticketRun['conversation_id'] }}" class="space-y-3">
            @include('partials.assistant-recommendations', [
                'prompt' => $ticketRun['prompt'],
                'recommendations' => $ticketRun['recommendations'],
                'response' => $ticketRun['response'],
            ])
        </div>
    @endforeach
</div>
