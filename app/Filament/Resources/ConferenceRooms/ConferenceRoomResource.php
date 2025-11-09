<?php

namespace App\Filament\Resources\ConferenceRooms;

use App\Filament\Resources\ConferenceRooms\Pages\CreateConferenceRoom;
use App\Filament\Resources\ConferenceRooms\Pages\EditConferenceRoom;
use App\Filament\Resources\ConferenceRooms\Pages\ListConferenceRooms;
use App\Filament\Resources\ConferenceRooms\Schemas\ConferenceRoomForm;
use App\Filament\Resources\ConferenceRooms\Tables\ConferenceRoomsTable;
use App\Models\ConferenceRoom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConferenceRoomResource extends Resource
{
    protected static ?string $model = ConferenceRoom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

    protected static ?string $recordTitleAttribute = 'ConferenceRoom';

    protected static ?int $navigationSort = 200;

    public static function form(Schema $schema): Schema
    {
        return ConferenceRoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConferenceRoomsTable::configure($table);
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
            'index' => ListConferenceRooms::route('/'),
            'create' => CreateConferenceRoom::route('/create'),
            'edit' => EditConferenceRoom::route('/{record}/edit'),
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
