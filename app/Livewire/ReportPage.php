<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use Flux\DateRange;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class ReportPage extends Component
{
    public ?DateRange $range = null;

    public int $totalConversations = 0;

    public int $uniqueUsers = 0;

    public int $totalMessages = 0;

    public int $daysInRange = 1;

    public float $averagePerDay = 0.0;

    public string $rangeLabel = '';

    /** @var array<int, array<string, mixed>> */
    public array $chartData = [];

    public function mount(): void
    {
        $this->range ??= new DateRange(
            now()->subDays(6)->startOfDay(),
            now()->endOfDay(),
        );

        $this->refreshStats();
    }

    public function updatedRange(): void
    {
        if ($this->range) {
            $this->range = new DateRange(
                $this->range->start()->copy()->startOfDay(),
                $this->range->end()->copy()->endOfDay(),
            );
        }

        $this->refreshStats();
    }

    public function render(): View
    {
        return view('livewire.report-page')->layout('components.layouts.app');
    }

    protected function refreshStats(): void
    {
        $bounds = $this->rangeBounds();

        $conversations = Conversation::query()
            ->when($bounds, fn ($query) => $query->whereBetween('created_at', $bounds))
            ->get();

        $this->totalConversations = $conversations->count();
        $this->uniqueUsers = $conversations->pluck('user_id')->filter()->unique()->count();

        $this->totalMessages = Message::query()
            ->when($bounds, fn ($query) => $query->whereBetween('created_at', $bounds))
            ->count();

        $this->chartData = $this->formatChartData(
            $this->buildDailyCounts($conversations)
        );

        $this->daysInRange = $this->range ? max(1, $this->range->count()) : 1;
        $this->averagePerDay = $this->daysInRange ? round($this->totalConversations / $this->daysInRange, 1) : 0;
        $this->rangeLabel = $this->range
            ? sprintf(
                '%s — %s',
                $this->range->start()->format('M j, Y'),
                $this->range->end()->format('M j, Y'),
            )
            : 'All time';
    }

    protected function buildDailyCounts(Collection $conversations): Collection
    {
        if (! $this->range) {
            return collect();
        }

        $grouped = $conversations->groupBy(fn (Conversation $conversation) => $conversation->created_at->toDateString());

        $days = collect();

        foreach ($this->range as $date) {
            $day = $date->toDateString();
            $days->put($day, $grouped->get($day, collect())->count());
        }

        return $days;
    }

    /**
     * @param  Collection<string, int>  $dailyCounts
     * @return array<int, array<string, string|int>>
     */
    protected function formatChartData(Collection $dailyCounts): array
    {
        return $dailyCounts
            ->map(fn (int $count, string $day) => [
                'date' => $day,
                'conversations' => $count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, \Carbon\CarbonInterface>|null
     */
    protected function rangeBounds(): ?array
    {
        if (! $this->range) {
            return null;
        }

        return [
            $this->range->start()->copy()->startOfDay(),
            $this->range->end()->copy()->endOfDay(),
        ];
    }
}
