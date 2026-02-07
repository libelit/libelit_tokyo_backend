<?php

namespace App\Filament\Widgets;

use App\Models\Investment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class InvestmentTrendsChart extends ChartWidget
{
    protected ?string $heading = 'Investment Trends';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'half';

    protected ?string $maxHeight = '300px';

    public ?string $filter = '6months';

    protected function getFilters(): ?array
    {
        return [
            '30days' => 'Last 30 Days',
            '3months' => 'Last 3 Months',
            '6months' => 'Last 6 Months',
            '12months' => 'Last 12 Months',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        $endDate = now();
        $startDate = match ($filter) {
            '30days' => now()->subDays(30),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            '12months' => now()->subMonths(12),
            default => now()->subMonths(6),
        };

        $investments = Investment::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $totals = [];
        $counts = [];

        $current = $startDate->copy()->startOfMonth();
        while ($current <= $endDate) {
            $monthKey = $current->format('Y-m');
            $labels[] = $current->format('M Y');

            $investment = $investments->firstWhere('month', $monthKey);
            $totals[] = $investment ? (float) $investment->total : 0;
            $counts[] = $investment ? $investment->count : 0;

            $current->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Investment Amount ($)',
                    'data' => $totals,
                    'borderColor' => '#14B8A6',
                    'backgroundColor' => 'rgba(20, 184, 166, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => '(value) => "$" + value.toLocaleString()',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => '(context) => "$" + context.raw.toLocaleString()',
                    ],
                ],
            ],
        ];
    }
}
