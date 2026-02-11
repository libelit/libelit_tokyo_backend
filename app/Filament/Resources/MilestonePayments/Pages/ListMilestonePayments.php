<?php

namespace App\Filament\Resources\MilestonePayments\Pages;

use App\Enums\MilestoneStatusEnum;
use App\Filament\Resources\MilestonePayments\MilestonePaymentResource;
use App\Models\ProjectMilestone;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMilestonePayments extends ListRecords
{
    protected static string $resource = MilestonePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(ProjectMilestone::whereIn('status', [
                    MilestoneStatusEnum::APPROVED,
                    MilestoneStatusEnum::PAID,
                ])->count()),

            'pending_payment' => Tab::make('Pending Payment')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', MilestoneStatusEnum::APPROVED))
                ->badge(ProjectMilestone::where('status', MilestoneStatusEnum::APPROVED)->count())
                ->badgeColor('warning'),

            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', MilestoneStatusEnum::PAID))
                ->badge(ProjectMilestone::where('status', MilestoneStatusEnum::PAID)->count())
                ->badgeColor('success'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\MilestonePayments\Widgets\MilestonePaymentStatsWidget::class,
        ];
    }
}
