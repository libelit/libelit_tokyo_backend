<?php

namespace App\Filament\Resources\Investors;

use App\Filament\Resources\Investors\Pages\CreateInvestor;
use App\Filament\Resources\Investors\Pages\EditInvestor;
use App\Filament\Resources\Investors\Pages\ListInvestors;
use App\Filament\Resources\Investors\Pages\ViewInvestor;
use App\Filament\Resources\Investors\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Investors\RelationManagers\InvestmentsRelationManager;
use App\Filament\Resources\Investors\Schemas\InvestorForm;
use App\Filament\Resources\Investors\Tables\InvestorsTable;
use App\Models\InvestorProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvestorResource extends Resource
{
    protected static ?string $model = InvestorProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static \UnitEnum|string|null $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Investors';

    protected static ?string $modelLabel = 'Investor';

    protected static ?string $pluralModelLabel = 'Investors';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return InvestorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvestorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
            InvestmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvestors::route('/'),
            'create' => CreateInvestor::route('/create'),
            'view' => ViewInvestor::route('/{record}'),
            'edit' => EditInvestor::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
