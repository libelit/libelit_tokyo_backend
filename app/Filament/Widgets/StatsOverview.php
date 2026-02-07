<?php

namespace App\Filament\Widgets;

use App\Enums\KybStatusEnum;
use App\Enums\KycStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\LenderProfile;
use App\Models\Project;
use App\Models\DeveloperProfile;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Developers', DeveloperProfile::count())
                ->description(DeveloperProfile::where('kyb_status', KybStatusEnum::PENDING)->count() . ' pending KYB')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Lenders', LenderProfile::count())
                ->description(LenderProfile::where('kyc_status', KycStatusEnum::PENDING)->count() . ' pending KYC')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Projects', Project::count())
                ->description(Project::where('status', ProjectStatusEnum::SUBMITTED)->count() . ' awaiting review')
                ->descriptionIcon('heroicon-m-home')
                ->color('warning'),
        ];
    }
}
