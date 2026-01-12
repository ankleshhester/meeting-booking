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
                // Added emp_code field unique to Attendees
                TextInput::make('emp_code')
                    ->label('Employee Code')
                    ->unique(table: 'attendees', ignorable: fn ($record) => $record)
                    ->required(),
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
