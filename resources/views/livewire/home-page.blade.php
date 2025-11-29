@php use Illuminate\Support\Str; @endphp

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">Ticky</flux:heading>
        <flux:button tag="a" href="{{ route('triage') }}" variant="primary" icon="paper-airplane" wire:navigate class="cursor-pointer">
            Triage ticket
        </flux:button>
    </div>

    @if ($conversations->isEmpty())
        <flux:callout variant="secondary" icon="information-circle">
            No conversations yet. Start a triage to see history here.
        </flux:callout>
    @else
        <div class="space-y-3">
            @foreach ($conversations as $conversation)
                @php
                    $firstMessage = $conversation->messages->sortBy('created_at')->first();
                @endphp
                <flux:card
                    class="cursor-pointer"
                    wire:click="openConversation({{ $conversation->id }})"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <flux:heading size="md" level="2">Conversation #{{ $conversation->id }}</flux:heading>
                            <flux:text>
                                {{ $firstMessage ? Str::limit($firstMessage->content, 120) : 'No messages yet.' }}
                            </flux:text>
                        </div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $conversation->created_at->diffForHumans() }}
                        </flux:text>
                    </div>
                </flux:card>
            @endforeach
            <flux:pagination :paginator="$conversations" class="pt-2" />
        </div>
    @endif

    <flux:modal
        wire:model="showConversation"
        variant="flyout"
        class="md:w-xl"
        position="right"
        :dismissible="true"
    >
        <div class="space-y-4">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:heading size="lg">
                        Conversation #{{ $activeConversation?->id }}
                    </flux:heading>
                    @if ($activeConversation)
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            Started {{ $activeConversation->created_at->diffForHumans() }}
                        </flux:text>
                    @endif
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" class="cursor-pointer" />
                </flux:modal.close>
            </div>

            <div class="space-y-3">
                @forelse ($activeMessages as $message)
                    @php
                        $hasRecommendations = isset($message['recommendations']) && count($message['recommendations']);
                    @endphp
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <flux:heading size="sm">{{ $message['from'] }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $message['at']->diffForHumans() }}
                            </flux:text>
                        </div>

                        @if ($hasRecommendations)
                            @include('partials.assistant-recommendations', ['recommendations' => $message['recommendations']])
                        @else
                            <flux:callout
                                :variant="$message['from'] === 'You' ? 'subtle' : 'secondary'"
                                icon="{{ $message['from'] === 'You' ? 'user' : 'sparkles' }}"
                            >
                                <flux:text class="whitespace-pre-wrap">
                                    {{ $message['content'] }}
                                </flux:text>
                            </flux:callout>
                        @endif
                    </div>
                @empty
                    <flux:text>No messages in this conversation yet.</flux:text>
                @endforelse
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="primary" class="cursor-pointer">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
