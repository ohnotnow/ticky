@php
    $start = $range?->start();
    $end = $range?->end();
    $daysInRange = $range ? max(1, $range->count()) : 1;
    $averagePerDay = $daysInRange ? round($totalConversations / $daysInRange, 1) : 0;
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon.sparkles class="text-blue-500" />
                <flux:text class="text-sm">Reporting</flux:text>
            </div>
            <flux:heading size="xl" level="1">Usage insights</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                Track how triage is being used and who is engaging with it.
            </flux:text>
            <div class="flex flex-wrap items-center gap-2">
                @if ($range)
                    <flux:badge color="blue" icon="clock" variant="pill">
                        {{ $start?->format('M j, Y') }} — {{ $end?->format('M j, Y') }}
                    </flux:badge>
                @endif
                <flux:badge color="green" icon="arrow-trending-up" variant="pill">
                    Avg {{ $averagePerDay }} tickets/day
                </flux:badge>
            </div>
        </div>

        <flux:card class="max-w-md w-full md:w-auto">
            <div class="space-y-2">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Date range</flux:text>
                <flux:date-picker
                    wire:model="range"
                    mode="range"
                    with-presets
                    class="cursor-pointer"
                />
            </div>
        </flux:card>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <flux:text>Tickets triaged</flux:text>
                    <flux:heading size="xl" class="tabular-nums">
                        {{ number_format($totalConversations) }}
                    </flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $daysInRange }} day window
                    </flux:text>
                </div>
                <flux:icon.chat-bubble-bottom-center-text class="text-blue-500" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <flux:text>Unique users</flux:text>
                    <flux:heading size="xl" class="tabular-nums">
                        {{ number_format($uniqueUsers) }}
                    </flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Based on conversation authors
                    </flux:text>
                </div>
                <flux:icon.users class="text-emerald-500" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <flux:text>Messages saved</flux:text>
                    <flux:heading size="xl" class="tabular-nums">
                        {{ number_format($totalMessages) }}
                    </flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        User + assistant messages in range
                    </flux:text>
                </div>
                <flux:icon.inbox-arrow-down class="text-amber-500" />
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex items-center justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="md">Triage activity over time</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    Conversations per day in the selected window.
                </flux:text>
            </div>
            <flux:badge color="purple" icon="chart-bar" variant="pill">
                Daily trend
            </flux:badge>
        </div>

        <div class="mt-4">
            @if (empty($chartData))
                <flux:callout variant="secondary" icon="chart-bar">
                    <flux:text>No conversations in this range yet. Try widening the window.</flux:text>
                </flux:callout>
            @else
                <flux:chart wire:model="chartData" class="aspect-[4/2] w-full">
                    <flux:chart.svg>
                        <flux:chart.line field="conversations" class="text-blue-500" />
                        <flux:chart.point field="conversations" class="text-blue-400" />
                        <flux:chart.axis axis="x" field="date">
                            <flux:chart.axis.tick />
                            <flux:chart.axis.line />
                        </flux:chart.axis>
                        <flux:chart.axis axis="y">
                            <flux:chart.axis.grid />
                            <flux:chart.axis.tick />
                        </flux:chart.axis>
                    </flux:chart.svg>
                </flux:chart>
            @endif
        </div>
    </flux:card>
</div>
