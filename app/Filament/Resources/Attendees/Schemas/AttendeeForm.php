<?php

namespace App\Filament\Resources\Attendees\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttendeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('name'),
                TextInput::make('comment'),
                TextInput::make('response_status'),
                TextInput::make('owner_id')
                    ->numeric(),
            ]);
    }
}
