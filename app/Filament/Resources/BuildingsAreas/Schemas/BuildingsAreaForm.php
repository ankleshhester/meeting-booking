<?php

namespace App\Filament\Resources\BuildingsAreas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;

class BuildingsAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                ->label('Area Name')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('floors')
                    ->numeric()
                    ->label('No. of Floors')
                    ->required(),

                Forms\Components\Textarea::make('address')
                    ->label('Address')
                    ->rows(3),

                Forms\Components\Textarea::make('description')
                    ->rows(3),

                Hidden::make('owner_id')
                ->default(Auth::id())
                ->required(),
            ]);
    }
}
