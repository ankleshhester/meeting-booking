<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\ToggleColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_approved')
                    ->boolean()
                    ->sortable()
                    ->label('Approved'), // Added a clearer label
                ToggleColumn::make('is_approved')
                    ->label('Approved')
                    ->sortable(),
                TextColumn::make('emp_code')
                    ->searchable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('google_id')
                    ->searchable(),
                TextColumn::make('avatar')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve User')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    // Only show this action if the user is NOT approved
                    ->hidden(fn ($record): bool => $record->is_approved)
                    // The action logic
                    ->action(function ($record) {
                        $record->update(['is_approved' => true]);

                        Notification::make()
                            ->title('User Approved')
                            ->body("{$record->name} has been successfully approved and can now access the admin panel.")
                            ->success()
                            ->send();
                    }),
                // Existing Edit Action
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
