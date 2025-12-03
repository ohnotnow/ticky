<div class="space-y-6">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <flux:heading class="hidden md:block" size="xl" level="1">Ticky</flux:heading>

        <div class="flex-1 flex flex-col md:flex-row items-center justify-center gap-4 max-w-3xl">
            <flux:input
                wire:model.live="filter"
                placeholder="Search conversations…"
                icon="magnifying-glass"
                class="flex-1"
            />
            <flux:switch
                wire:model.live="showAll"
                label="Show all"
                class="cursor-pointer"
            />
        </div>

        <flux:button tag="a" href="{{ route('triage') }}" variant="primary" icon="paper-airplane" wire:navigate class="cursor-pointer">
            Triage ticket
        </flux:button>
    </div>

    <flux:separator class="my-4" />

    @if ($conversations->isEmpty())
        <flux:callout variant="secondary" icon="information-circle">
            No conversations yet. Start a triage to see history here.
        </flux:callout>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($conversations as $conversation)
                @php
                    $firstMessage = $conversation->messages->sortBy('created_at')->first();
                @endphp
                <flux:card
                    class="cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-600"
                    wire:click="openConversation({{ $conversation->id }})"
                    wire:key="conversation-{{ $conversation->id }}"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex justify-between items-center gap-2">
                                <flux:badge variant="pill">
                                    {{ $conversation->created_at->diffForHumans() }}
                                </flux:badge>
                                @if ($conversation->user_id !== Auth::id())
                                    <flux:avatar size="xs" title="{{ $conversation->user->full_name }}" name="{{ $conversation->user->full_name }}" />
                                @endif
                            </div>
                            <flux:text>
                                {{ $firstMessage ? Str::limit($firstMessage->content, 120) : 'No messages yet.' }}
                            </flux:text>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
        <flux:pagination :paginator="$conversations" class="pt-2" />
    @endif

    <flux:modal
        wire:model="showConversation"
        variant="flyout"
        class="w-3/4 md:w-xl"
        position="right"
        :dismissible="true"
    >
        <div class="space-y-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="space-y-1">
                    <flux:heading size="lg">
                        Conversation #{{ $activeConversation?->id }}
                    </flux:heading>
                </div>
            </div>

            <div class="space-y-3">
                @forelse ($activeMessages as $message)
                    @php
                        $hasRecommendations = isset($message['recommendations']) && count($message['recommendations']);
                    @endphp
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            @if ($message['model'])
                                <flux:badge variant="pill" size="sm">{{ $message['model'] }}</flux:badge>
                            @else
                                <flux:heading size="sm">{{ $message['from'] }}</flux:heading>
                            @endif
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $message['at']->diffForHumans() }}
                            </flux:text>
                        </div>

                        @if ($hasRecommendations)
                            @include('partials.assistant-recommendations', ['recommendations' => $message['recommendations']])
                        @else
                            <flux:callout
                                :variant="$message['from'] === 'User' ? 'subtle' : 'secondary'"
                                icon="{{ $message['from'] === 'User' ? 'user' : 'sparkles' }}"
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

            <div class="flex justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    @if ($activeConversation)
                        <flux:button
                            href="{{ route('conversations.download.markdown', $activeConversation) }}"
                            variant="subtle"
                            icon="document-text"
                            class="cursor-pointer"
                            title="Download markdown"
                        >
                        </flux:button>

                        <flux:button
                            href="{{ route('conversations.download.json', $activeConversation) }}"
                            variant="subtle"
                            icon="code-bracket"
                            class="cursor-pointer"
                            title="Download JSON"
                        >
                        </flux:button>
                    @endif
                </div>
                <flux:modal.close>
                    <flux:button variant="primary" class="cursor-pointer">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
