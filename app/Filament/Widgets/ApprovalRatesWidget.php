<?php

namespace App\Filament\Widgets;

use App\Enums\KybStatusEnum;
use App\Enums\KycStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\InvestorProfile;
use App\Models\Project;
use App\Models\DeveloperProfile;
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

        // KYC Stats
        $totalKyc = InvestorProfile::count();
        $approvedKyc = InvestorProfile::where('kyc_status', KycStatusEnum::APPROVED)->count();
        $kycRate = $totalKyc > 0 ? round(($approvedKyc / $totalKyc) * 100, 1) : 0;

        // Project Stats
        $totalProjects = Project::whereNotIn('status', [ProjectStatusEnum::DRAFT])->count();
        $approvedProjects = Project::whereIn('status', [
            ProjectStatusEnum::APPROVED,
            ProjectStatusEnum::FUNDED,
            ProjectStatusEnum::COMPLETED,
        ])->count();
        $projectRate = $totalProjects > 0 ? round(($approvedProjects / $totalProjects) * 100, 1) : 0;

        // Investment totals
        $totalInvestments = \App\Models\Investment::sum('amount');

        return [
            Stat::make('KYB Approval Rate', $kybRate . '%')
                ->description($approvedKyb . ' of ' . $totalKyb . ' approved')
                ->descriptionIcon('heroicon-m-building-office')
                ->color($kybRate >= 70 ? 'success' : ($kybRate >= 40 ? 'warning' : 'danger'))
                ->chart([7, 3, 4, 5, 6, $kybRate]),

            Stat::make('KYC Approval Rate', $kycRate . '%')
                ->description($approvedKyc . ' of ' . $totalKyc . ' approved')
                ->descriptionIcon('heroicon-m-identification')
                ->color($kycRate >= 70 ? 'success' : ($kycRate >= 40 ? 'warning' : 'danger'))
                ->chart([5, 4, 6, 5, 7, $kycRate]),

            Stat::make('Project Approval Rate', $projectRate . '%')
                ->description($approvedProjects . ' of ' . $totalProjects . ' approved')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color($projectRate >= 70 ? 'success' : ($projectRate >= 40 ? 'warning' : 'danger'))
                ->chart([4, 5, 3, 6, 5, $projectRate]),

            Stat::make('Total Investments', '$' . number_format($totalInvestments, 2))
                ->description('All time investment volume')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
