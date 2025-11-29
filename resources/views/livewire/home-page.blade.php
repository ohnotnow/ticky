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
                <flux:card>
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
        </div>
    @endif
</div>
