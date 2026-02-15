<?php

namespace App\Filament\Resources\Meetings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\View\TablesIconAlias;
use Carbon\Carbon;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) =>
                $query->whereNull('parent_meeting_id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Meeting')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->sortable()
                    ->formatStateUsing(fn ($state) =>
                        $state ? Carbon::parse($state)->format('h:i A') : null
                    ),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->sortable()
                    ->formatStateUsing(fn ($state) =>
                        $state ? Carbon::parse($state)->format('h:i A') : null
                    ),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration (mins)')
                    ->getStateUsing(fn ($record) =>
                        $record->end_time && $record->start_time
                            ? $record->start_time->diffInMinutes($record->end_time)
                            : null
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('host.name')
                    ->label('Organizer')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('rooms.name')
                    ->label('Room')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(config('project.datetime_format'))
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(function ($record) {

            if (! $record->date || ! $record->end_time) {
                return false;
            }

            // Extract time only from end_time
            $endTime = $record->end_time instanceof \Carbon\Carbon
                ? $record->end_time->format('H:i:s')
                : \Carbon\Carbon::parse($record->end_time)->format('H:i:s');

            // Build final end datetime using record->date + extracted time
            $endDateTime = \Carbon\Carbon::parse(
                $record->date . ' ' . $endTime
            );

            // Allow edit until 2 hours after meeting end
            return now()->lessThanOrEqualTo(
                $endDateTime->copy()->addHours(2)
            );
        }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
