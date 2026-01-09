<?php

namespace App\Filament\Resources\EmployeeCostMasters;

use App\Filament\Resources\EmployeeCostMasters\Pages\CreateEmployeeCostMaster;
use App\Filament\Resources\EmployeeCostMasters\Pages\EditEmployeeCostMaster;
use App\Filament\Resources\EmployeeCostMasters\Pages\ListEmployeeCostMasters;
use App\Filament\Resources\EmployeeCostMasters\Schemas\EmployeeCostMasterForm;
use App\Filament\Resources\EmployeeCostMasters\Tables\EmployeeCostMastersTable;
use App\Models\EmployeeCostMaster;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeCostMasterResource extends Resource
{
    protected static ?string $model = EmployeeCostMaster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'EmployeeCostMaster';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return EmployeeCostMasterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeCostMastersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeCostMasters::route('/'),
            'create' => CreateEmployeeCostMaster::route('/create'),
            'edit' => EditEmployeeCostMaster::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
