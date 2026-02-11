<?php

namespace App\Filament\Resources\MilestonePayments;

use App\Enums\MilestoneStatusEnum;
use App\Filament\Resources\MilestonePayments\Pages\ListMilestonePayments;
use App\Filament\Resources\MilestonePayments\Pages\ViewMilestonePayment;
use App\Models\ProjectMilestone;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MilestonePaymentResource extends Resource
{
    protected static ?string $model = ProjectMilestone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static \UnitEnum|string|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Milestone Payments';

    protected static ?string $modelLabel = 'Milestone Payment';

    protected static ?string $pluralModelLabel = 'Milestone Payments';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['project.developer', 'project.lender', 'approver'])
            ->whereIn('status', [
                MilestoneStatusEnum::APPROVED,
                MilestoneStatusEnum::PAID,
            ])
            ->orderByDesc('approved_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.title')
                    ->label('Project')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->project?->title)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.developer.company_name')
                    ->label('Developer')
                    ->limit(20)
                    ->sortable(),

                TextColumn::make('project.lender.company_name')
                    ->label('Lender')
                    ->limit(20)
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Milestone')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->title)
                    ->searchable(),

                TextColumn::make('sequence')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (MilestoneStatusEnum $state): string => match ($state) {
                        MilestoneStatusEnum::APPROVED => 'warning',
                        MilestoneStatusEnum::PAID => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime('M j, Y')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid')
                    ->dateTime('M j, Y')
                    ->placeholder('Pending payment')
                    ->sortable(),

                TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->limit(15)
                    ->placeholder('-')
                    ->copyable(),
            ])
            ->defaultSort('approved_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        MilestoneStatusEnum::APPROVED->value => 'Approved (Awaiting Payment)',
                        MilestoneStatusEnum::PAID->value => 'Paid',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project Details')
                    ->schema([
                        Placeholder::make('project_title')
                            ->label('Project')
                            ->content(fn ($record) => $record?->project?->title ?? '-'),
                        Placeholder::make('developer')
                            ->label('Developer')
                            ->content(fn ($record) => $record?->project?->developer?->company_name ?? '-'),
                        Placeholder::make('lender')
                            ->label('Lender')
                            ->content(fn ($record) => $record?->project?->lender?->company_name ?? '-'),
                        Placeholder::make('loan_amount')
                            ->label('Total Loan Amount')
                            ->content(fn ($record) => '$' . number_format($record?->project?->loan_amount ?? 0, 2)),
                    ])
                    ->columns(4),

                Section::make('Milestone Details')
                    ->schema([
                        Placeholder::make('title')
                            ->label('Milestone')
                            ->content(fn ($record) => $record?->title ?? '-'),
                        Placeholder::make('sequence')
                            ->label('Sequence')
                            ->content(fn ($record) => 'Milestone #' . ($record?->sequence ?? '-')),
                        Placeholder::make('description')
                            ->label('Description')
                            ->content(fn ($record) => $record?->description ?? 'No description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Payment Information')
                    ->schema([
                        Placeholder::make('amount')
                            ->label('Payment Amount')
                            ->content(fn ($record) => '$' . number_format($record?->amount ?? 0, 2)),
                        Placeholder::make('percentage')
                            ->label('% of Total Loan')
                            ->content(fn ($record) => ($record?->percentage ?? $record?->calculatePercentage() ?? 0) . '%'),
                        Placeholder::make('status')
                            ->label('Status')
                            ->content(fn ($record) => $record?->status?->getLabel() ?? '-'),
                        Placeholder::make('approved_at')
                            ->label('Approved At')
                            ->content(fn ($record) => $record?->approved_at?->format('M j, Y H:i') ?? '-'),
                        Placeholder::make('approver')
                            ->label('Approved By')
                            ->content(fn ($record) => $record?->approver?->name ?? '-'),
                        Placeholder::make('paid_at')
                            ->label('Paid At')
                            ->content(fn ($record) => $record?->paid_at?->format('M j, Y H:i') ?? 'Pending payment'),
                        Placeholder::make('payment_reference')
                            ->label('Payment Reference')
                            ->content(fn ($record) => $record?->payment_reference ?? '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Progress')
                    ->schema([
                        Placeholder::make('project_progress')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record?->project) {
                                    return '-';
                                }

                                $project = $record->project;
                                $totalMilestones = $project->milestones()->count();
                                $paidMilestones = $project->milestones()
                                    ->where('status', MilestoneStatusEnum::PAID)
                                    ->count();
                                $totalAmount = $project->loan_amount;
                                $paidAmount = $project->milestones()
                                    ->where('status', MilestoneStatusEnum::PAID)
                                    ->sum('amount');
                                $remainingAmount = $totalAmount - $paidAmount;

                                return "Milestones: {$paidMilestones} of {$totalMilestones} paid | " .
                                       "Paid: \$" . number_format($paidAmount, 2) . " | " .
                                       "Remaining: \$" . number_format($remainingAmount, 2);
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMilestonePayments::route('/'),
            'view' => ViewMilestonePayment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Show count of approved but not yet paid milestones
        $pendingPayment = ProjectMilestone::where('status', MilestoneStatusEnum::APPROVED)->count();
        return $pendingPayment > 0 ? (string) $pendingPayment : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
