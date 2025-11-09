<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                // Select Role
                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple(),
                DateTimePicker::make('email_verified_at'),
                Toggle::make('is_approved')
                        ->label('Approved?')
                        ->default(false),
                TextInput::make('emp_code'),
                TextInput::make('department'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at'),
                TextInput::make('google_id'),
                TextInput::make('avatar'),
            ]);
    }
}
