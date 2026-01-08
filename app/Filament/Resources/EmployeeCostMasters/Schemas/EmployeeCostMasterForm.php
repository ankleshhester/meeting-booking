<?php

namespace App\Filament\Resources\EmployeeCostMasters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeCostMasterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('emp_code')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('cost_per_hour')
                    ->label('CTC')
                    ->required()
                    ->numeric(),
                TextInput::make('owner_id')
                    ->numeric(),
            ]);
    }
}
