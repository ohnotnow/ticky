<div class="space-y-6">
    <div class="space-y-2">
        <div class="flex items-center gap-3 flex-wrap">
            <flux:heading size="xl" level="1">Ticket triage</flux:heading>
            <flux:badge variant="pill">
                {{ $this->selectedModelLabel }}
            </flux:badge>
        </div>
        <flux:text>
            Paste a support ticket and ask the assistant where it should go.
        </flux:text>
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
                <flux:button size="sm" variant="subtle" icon="adjustments-horizontal" class="cursor-pointer" wire:click="openModelPicker" />
            </x-slot>

            <x-slot name="actionsTrailing">
                <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" class="cursor-pointer">
                    Send
                </flux:button>
            </x-slot>
        </flux:composer>
    </form>

    <flux:modal
        wire:model="showModelPicker"
        variant="flyout"
        position="right"
        class="md:w-lg"
    >
        <div class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">Choose model</flux:heading>
                <flux:text class="mt-1">
                    Select the provider and model to use for this submission. If you do not pick one, we will use the default ({{ config('ticky.llm_model') }}).
                </flux:text>
            </div>

            <flux:radio.group
                wire:model.live="selectedModel"
                label="Models"
                description="Pick a provider and model for the assistant."
                variant="cards"
                class="flex-col"
            >
                @foreach ($this->modelChoices as $provider => $models)
                    <div class="space-y-2">
                        <flux:text variant="strong">{{ ucfirst($provider) }}</flux:text>
                        @foreach ($models as $model => $details)
                            <flux:radio
                                value="{{ $provider }}/{{ $model }}"
                                label="{{ $details['label'] }}"
                                description="{{ $details['description'] }}"
                            />
                        @endforeach
                    </div>
                @endforeach
            </flux:radio.group>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="clearModelSelection" variant="primary" class="cursor-pointer">
                    Use default ({{ config('ticky.llm_model') }})
                </flux:button>
            </div>
        </div>
    </flux:modal>

    @foreach ($ticketRuns as $ticketRun)
        <div wire:key="ticket-run-{{ $ticketRun['conversation_id'] }}" class="space-y-3">
            @include('partials.assistant-recommendations', [
                'prompt' => $ticketRun['prompt'],
                'recommendations' => $ticketRun['recommendations'],
                'response' => $ticketRun['response'],
            ])

            <div class="flex items-center gap-2">
                <flux:button
                    tag="a"
                    href="{{ route('conversations.download.markdown', $ticketRun['conversation_id']) }}"
                    size="sm"
                    variant="subtle"
                    icon="document-text"
                    class="cursor-pointer"
                    title="Download markdown"
                >
                </flux:button>

                <flux:button
                    tag="a"
                    href="{{ route('conversations.download.json', $ticketRun['conversation_id']) }}"
                    size="sm"
                    variant="ghost"
                    icon="code-bracket"
                    class="cursor-pointer"
                    title="Download JSON"
                >
                </flux:button>
            </div>
        </div>
    @endforeach
</div>
