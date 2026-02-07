<?php

namespace App\Filament\Widgets;

use App\Enums\KybStatusEnum;
use App\Enums\KycStatusEnum;
use App\Models\LenderProfile;
use App\Models\DeveloperProfile;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingApprovalsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pending KYB/KYC Approvals';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DeveloperProfile::query()
                    ->where('kyb_status', KybStatusEnum::PENDING)
                    ->orWhere('kyb_status', KybStatusEnum::UNDER_REVIEW)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('kyb_status')
                    ->badge()
                    ->color(fn (KybStatusEnum $state): string => match ($state) {
                        KybStatusEnum::PENDING => 'warning',
                        KybStatusEnum::UNDER_REVIEW => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('kyb_submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.developers.view', $record))
            ->defaultSort('kyb_submitted_at', 'asc')
            ->paginated([5, 10, 25]);
    }
}
