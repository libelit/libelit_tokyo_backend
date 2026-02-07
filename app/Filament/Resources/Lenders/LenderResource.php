<?php

namespace App\Filament\Resources\Lenders;

use App\Filament\Resources\Lenders\Pages\CreateLender;
use App\Filament\Resources\Lenders\Pages\EditLender;
use App\Filament\Resources\Lenders\Pages\ListLenders;
use App\Filament\Resources\Lenders\Pages\ViewLender;
use App\Filament\Resources\Lenders\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Lenders\RelationManagers\InvestmentsRelationManager;
use App\Filament\Resources\Lenders\Schemas\LenderForm;
use App\Filament\Resources\Lenders\Tables\LendersTable;
use App\Models\LenderProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LenderResource extends Resource
{
    protected static ?string $model = LenderProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static \UnitEnum|string|null $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Lenders';

    protected static ?string $modelLabel = 'Lender';

    protected static ?string $pluralModelLabel = 'Lenders';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LenderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LendersTable::configure($table);
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
            'index' => ListLenders::route('/'),
            'create' => CreateLender::route('/create'),
            'view' => ViewLender::route('/{record}'),
            'edit' => EditLender::route('/{record}/edit'),
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
