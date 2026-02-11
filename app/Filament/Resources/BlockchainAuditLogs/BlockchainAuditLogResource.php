<?php

namespace App\Filament\Resources\BlockchainAuditLogs;

use App\Enums\BlockchainAuditEventTypeEnum;
use App\Enums\XrplTxStatusEnum;
use App\Filament\Resources\BlockchainAuditLogs\Pages\ListBlockchainAuditLogs;
use App\Filament\Resources\BlockchainAuditLogs\Pages\ViewBlockchainAuditLog;
use App\Models\BlockchainAuditLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BlockchainAuditLogResource extends Resource
{
    protected static ?string $model = BlockchainAuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static \UnitEnum|string|null $navigationGroup = 'Blockchain';

    protected static ?string $navigationLabel = 'Audit Trail';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('auditable_type')
                    ->label('Entity Type')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->sortable(),

                TextColumn::make('auditable_id')
                    ->label('Entity ID')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (XrplTxStatusEnum $state): string => match ($state) {
                        XrplTxStatusEnum::PENDING => 'warning',
                        XrplTxStatusEnum::VALIDATED => 'success',
                        XrplTxStatusEnum::FAILED => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('tx_hash')
                    ->label('TX Hash')
                    ->limit(16)
                    ->tooltip(fn ($record) => $record->tx_hash)
                    ->url(fn ($record) => $record->explorer_url, shouldOpenInNewTab: true)
                    ->color('primary')
                    ->placeholder('Pending...'),

                TextColumn::make('user.name')
                    ->label('Triggered By')
                    ->placeholder('System'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options(BlockchainAuditEventTypeEnum::class),

                SelectFilter::make('status')
                    ->options(XrplTxStatusEnum::class),

                SelectFilter::make('auditable_type')
                    ->label('Entity Type')
                    ->options([
                        'App\\Models\\DeveloperProfile' => 'Developer',
                        'App\\Models\\LenderProfile' => 'Lender',
                        'App\\Models\\Project' => 'Project',
                        'App\\Models\\LoanProposal' => 'Loan Proposal',
                        'App\\Models\\ProjectMilestone' => 'Milestone',
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
                Section::make('Event Details')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('event_type')
                            ->label('Event Type')
                            ->content(fn ($record) => $record?->event_type?->getLabel() ?? '-'),
                        \Filament\Forms\Components\Placeholder::make('auditable_type')
                            ->label('Entity Type')
                            ->content(fn ($record) => $record ? class_basename($record->auditable_type) : '-'),
                        \Filament\Forms\Components\Placeholder::make('auditable_id')
                            ->label('Entity ID')
                            ->content(fn ($record) => $record?->auditable_id ?? '-'),
                        \Filament\Forms\Components\Placeholder::make('user_name')
                            ->label('Triggered By')
                            ->content(fn ($record) => $record?->user?->name ?? 'System'),
                        \Filament\Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn ($record) => $record?->created_at?->format('M j, Y H:i:s') ?? '-'),
                    ])
                    ->columns(3),

                Section::make('Blockchain Status')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('status')
                            ->label('Status')
                            ->content(fn ($record) => $record?->status?->getLabel() ?? '-'),
                        \Filament\Forms\Components\Placeholder::make('tx_hash')
                            ->label('Transaction Hash')
                            ->content(fn ($record) => $record?->tx_hash ?? 'Not submitted yet'),
                        \Filament\Forms\Components\Placeholder::make('data_hash')
                            ->label('Data Hash (SHA-256)')
                            ->content(fn ($record) => $record?->data_hash ?? '-'),
                        \Filament\Forms\Components\Placeholder::make('submitted_at')
                            ->label('Submitted At')
                            ->content(fn ($record) => $record?->submitted_at?->format('M j, Y H:i:s') ?? 'Not submitted'),
                        \Filament\Forms\Components\Placeholder::make('validated_at')
                            ->label('Validated At')
                            ->content(fn ($record) => $record?->validated_at?->format('M j, Y H:i:s') ?? 'Not validated'),
                        \Filament\Forms\Components\Placeholder::make('attempts')
                            ->label('Attempts')
                            ->content(fn ($record) => $record?->attempts ?? 0),
                        \Filament\Forms\Components\Placeholder::make('error_message')
                            ->label('Error')
                            ->content(fn ($record) => $record?->error_message ?? 'None')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Event Data')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('event_data')
                            ->label('')
                            ->content(fn ($record) => $record ? json_encode($record->event_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '-')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
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
            'index' => ListBlockchainAuditLogs::route('/'),
            'view' => ViewBlockchainAuditLog::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', XrplTxStatusEnum::PENDING)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
