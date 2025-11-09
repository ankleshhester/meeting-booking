<?php

namespace App\Filament\Resources\BuildingsAreas;

use App\Filament\Resources\BuildingsAreas\Pages\CreateBuildingsArea;
use App\Filament\Resources\BuildingsAreas\Pages\EditBuildingsArea;
use App\Filament\Resources\BuildingsAreas\Pages\ListBuildingsAreas;
use App\Filament\Resources\BuildingsAreas\Schemas\BuildingsAreaForm;
use App\Filament\Resources\BuildingsAreas\Tables\BuildingsAreasTable;
use App\Models\BuildingsArea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuildingsAreaResource extends Resource
{
    protected static ?string $model = BuildingsArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice;

    protected static ?string $recordTitleAttribute = 'BuildingsArea';

    protected static ?int $navigationSort = 500;

    public static function form(Schema $schema): Schema
    {
        return BuildingsAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BuildingsAreasTable::configure($table);
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
            'index' => ListBuildingsAreas::route('/'),
            'create' => CreateBuildingsArea::route('/create'),
            'edit' => EditBuildingsArea::route('/{record}/edit'),
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
