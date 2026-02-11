<?php

namespace App\Filament\Widgets;

use App\Enums\KybStatusEnum;
use App\Enums\MilestoneStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\DeveloperProfile;
use App\Models\LenderProfile;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApprovalRatesWidget extends BaseWidget
{
    protected ?string $heading = 'Approval Rates';

    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        // KYB Stats
        $totalKyb = DeveloperProfile::count();
        $approvedKyb = DeveloperProfile::where('kyb_status', KybStatusEnum::APPROVED)->count();
        $kybRate = $totalKyb > 0 ? round(($approvedKyb / $totalKyb) * 100, 1) : 0;

        // Lender KYB Stats
        $totalLenderKyb = LenderProfile::count();
        $approvedLenderKyb = LenderProfile::where('kyb_status', KybStatusEnum::APPROVED)->count();
        $lenderKybRate = $totalLenderKyb > 0 ? round(($approvedLenderKyb / $totalLenderKyb) * 100, 1) : 0;

        // Project Stats
        $totalProjects = Project::whereNotIn('status', [ProjectStatusEnum::DRAFT])->count();
        $approvedProjects = Project::whereIn('status', [
            ProjectStatusEnum::APPROVED,
            ProjectStatusEnum::FUNDED,
            ProjectStatusEnum::COMPLETED,
        ])->count();
        $projectRate = $totalProjects > 0 ? round(($approvedProjects / $totalProjects) * 100, 1) : 0;

        // Milestone payments totals (paid milestones)
        $totalMilestonePayments = ProjectMilestone::where('status', MilestoneStatusEnum::PAID)->sum('amount');

        return [
            Stat::make('KYB Approval Rate', $kybRate . '%')
                ->description($approvedKyb . ' of ' . $totalKyb . ' approved')
                ->descriptionIcon('heroicon-m-building-office')
                ->color($kybRate >= 70 ? 'success' : ($kybRate >= 40 ? 'warning' : 'danger'))
                ->chart([7, 3, 4, 5, 6, $kybRate]),

            Stat::make('Lender KYB Approval Rate', $lenderKybRate . '%')
                ->description($approvedLenderKyb . ' of ' . $totalLenderKyb . ' approved')
                ->descriptionIcon('heroicon-m-identification')
                ->color($lenderKybRate >= 70 ? 'success' : ($lenderKybRate >= 40 ? 'warning' : 'danger'))
                ->chart([5, 4, 6, 5, 7, $lenderKybRate]),

            Stat::make('Project Approval Rate', $projectRate . '%')
                ->description($approvedProjects . ' of ' . $totalProjects . ' approved')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color($projectRate >= 70 ? 'success' : ($projectRate >= 40 ? 'warning' : 'danger'))
                ->chart([4, 5, 3, 6, 5, $projectRate]),

            Stat::make('Milestone Payments', '$' . number_format($totalMilestonePayments, 2))
                ->description('Total milestone payments made')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
