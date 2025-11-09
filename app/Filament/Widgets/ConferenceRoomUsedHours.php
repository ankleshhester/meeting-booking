<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ConferenceRoom as Conference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ConferenceRoomUsedHours extends TableWidget
{
    protected static ?string $heading = 'Conferences Rooms by Usage Hours';

    protected int|string $defaultTableRecordsPerPage = 10;

    public static function canView(): bool
    {
        // Adjust the role check here if your admin role is named differently (e.g., 'admin')
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (Builder $query) => $this->getConferenceUsageQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Conference Name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Total Hours')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        $totalMinutes = (float)$state * 60;
                        if ($totalMinutes < 1) return '0:00';

                        $hours = floor($totalMinutes / 60);
                        $minutes = round($totalMinutes % 60);
                        if ($minutes == 60) {
                            $hours++;
                            $minutes = 0;
                        }
                        return $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
                    }),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // The query is already set up to join the 'meetings' table
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) =>
                                $query->whereDate('meetings.start_time', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $query, $date) =>
                                $query->whereDate('meetings.end_time', '<=', $date));
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make('conference-usage')
                            ->fromTable(),
                    ]),
            ]);
    }

    protected function getConferenceUsageQuery(): Builder
    {
        return Conference::withoutGlobalScopes()
            ->from('conference_rooms as rooms')
            ->select('rooms.id', 'rooms.name')
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, meetings.start_time, meetings.end_time)) / 60.0 AS total_hours')
            ->join('meetings', 'meetings.rooms_id', '=', 'rooms.id')
            ->groupBy('rooms.id', 'rooms.name')
            ->orderByDesc('total_hours')
            ->orderBy('rooms.id');
    }
}
