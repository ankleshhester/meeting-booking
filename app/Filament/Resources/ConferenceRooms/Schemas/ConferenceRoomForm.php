<?php

namespace App\Filament\Resources\ConferenceRooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;

class ConferenceRoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('building_area_id')
                ->label('Building Area')
                ->relationship('buildingArea', 'name')
                ->searchable()
                ->preload()
                ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Room Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('capacity')
                    ->label('Seating Capacity')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                Hidden::make('owner_id')
                    ->default(Auth::id())
                    ->required(),
            ]);
    }
}
