<?php

namespace App\Filament\Widgets;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Filament\Widgets\ChartWidget;

class ProjectStatusChart extends ChartWidget
{
    protected ?string $heading = 'Projects by Status';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'half';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = collect(ProjectStatusEnum::cases());

        $data = $statuses->map(function ($status) {
            return Project::where('status', $status)->count();
        });

        $labels = $statuses->map(fn ($status) => $status->getLabel());

        $colors = $statuses->map(fn ($status) => match ($status) {
            ProjectStatusEnum::DRAFT => '#9CA3AF',
            ProjectStatusEnum::SUBMITTED => '#3B82F6',
            ProjectStatusEnum::UNDER_REVIEW => '#F59E0B',
            ProjectStatusEnum::APPROVED => '#10B981',
            ProjectStatusEnum::LISTED => '#06B6D4',
            ProjectStatusEnum::PROPOSAL_ACCEPTED => '#22C55E',
            ProjectStatusEnum::REJECTED => '#EF4444',
            ProjectStatusEnum::FUNDING => '#FBBF24',
            ProjectStatusEnum::FUNDED => '#8B5CF6',
            ProjectStatusEnum::COMPLETED => '#14B8A6',
        });

        return [
            'datasets' => [
                [
                    'label' => 'Projects',
                    'data' => $data->toArray(),
                    'backgroundColor' => $colors->toArray(),
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
}
