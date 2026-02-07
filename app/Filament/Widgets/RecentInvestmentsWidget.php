<?php

namespace App\Filament\Widgets;

use App\Enums\InvestmentStatusEnum;
use App\Models\Investment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentInvestmentsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Investments';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Investment::query()
                    ->with(['lender.user', 'project'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('lender.user.name')
                    ->label('Lender')
                    ->limit(20),
                TextColumn::make('project.title')
                    ->label('Project')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->project?->title),
                TextColumn::make('amount')
                    ->money('USD'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (InvestmentStatusEnum $state): string => match ($state) {
                        InvestmentStatusEnum::PENDING => 'warning',
                        InvestmentStatusEnum::CONFIRMED => 'info',
                        InvestmentStatusEnum::COMPLETED => 'success',
                        InvestmentStatusEnum::REFUNDED => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y'),
            ])
            ->paginated(false);
    }
}
