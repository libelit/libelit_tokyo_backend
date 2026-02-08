<?php

namespace App\Filament\Widgets;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProjectsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Projects by Loan Amount';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Project::query()
                    ->whereIn('status', [
                        ProjectStatusEnum::APPROVED,
                        ProjectStatusEnum::FUNDED,
                        ProjectStatusEnum::COMPLETED,
                    ])
                    ->orderByDesc('loan_amount')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->title),
                TextColumn::make('developer.company_name')
                    ->label('Developer')
                    ->limit(15),
                TextColumn::make('loan_amount')
                    ->label('Loan Amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ProjectStatusEnum $state): string => match ($state) {
                        ProjectStatusEnum::APPROVED => 'success',
                        ProjectStatusEnum::FUNDED => 'primary',
                        ProjectStatusEnum::COMPLETED => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
