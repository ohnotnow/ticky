<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-col md:flex-row items-center gap-2">
            <flux:heading size="xl" level="1">Usage insights</flux:heading>
            <div class="hidden md:flex flex-wrap items-center gap-2">
                <flux:badge color="blue" icon="clock" variant="pill">
                    {{ $rangeLabel }}
                </flux:badge>
                <flux:badge color="green" icon="arrow-trending-up" variant="pill">
                    Avg {{ $averagePerDay }} tickets/day
                </flux:badge>
            </div>
        </div>
        <flux:date-picker
            wire:model.live="range"
            mode="range"
            with-presets
            class="cursor-pointer"
            size="sm"
        />
    </div>


    <flux:separator />

    <div class="grid gap-4 md:grid-cols-3 mt-2">
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
                <flux:icon.chat-bubble-bottom-center-text class="text-blue-500 size-12" />
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
                <flux:icon.users class="text-emerald-500 size-12" />
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
                <flux:icon.inbox-arrow-down class="text-amber-500 size-12" />
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
            <div class="flex items-center gap-2">
                @if (count($chartData) <= 1)
                    <flux:badge color="amber" icon="exclamation-triangle" variant="pill">
                        Not enough data to plot
                    </flux:badge>
                @endif
                <flux:badge color="purple" icon="chart-bar" variant="pill">
                    Daily trend
                </flux:badge>
            </div>
        </div>

        <div class="mt-4">
            @if (empty($chartData))
                <flux:callout variant="secondary" icon="chart-bar">
                    <flux:text>No conversations in this range yet. Try widening the window.</flux:text>
                </flux:callout>
            @else
                <flux:chart wire:key="chart-{{ $rangeLabel }}" wire:model="chartData" class="aspect-[4/2] w-full">
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
