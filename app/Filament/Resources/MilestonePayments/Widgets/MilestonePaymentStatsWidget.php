<?php

namespace App\Filament\Resources\MilestonePayments\Widgets;

use App\Enums\MilestoneStatusEnum;
use App\Models\ProjectMilestone;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MilestonePaymentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPaid = ProjectMilestone::where('status', MilestoneStatusEnum::PAID)->sum('amount');
        $pendingPayment = ProjectMilestone::where('status', MilestoneStatusEnum::APPROVED)->sum('amount');
        $pendingCount = ProjectMilestone::where('status', MilestoneStatusEnum::APPROVED)->count();
        $paidCount = ProjectMilestone::where('status', MilestoneStatusEnum::PAID)->count();

        return [
            Stat::make('Total Paid', '$' . number_format($totalPaid, 2))
                ->description($paidCount . ' milestones paid')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending Payment', '$' . number_format($pendingPayment, 2))
                ->description($pendingCount . ' milestones awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('This Month', '$' . number_format(
                ProjectMilestone::where('status', MilestoneStatusEnum::PAID)
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount'),
                2
            ))
                ->description('Paid this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
