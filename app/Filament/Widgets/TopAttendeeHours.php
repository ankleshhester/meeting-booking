<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Widgets\TableWidget;
use App\Models\Attendee;
use App\Models\Meeting;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopAttendeeHours extends TableWidget
{
    protected static ?string $heading = 'Attendees by Meeting Hours';

    protected int | string $defaultTableRecordsPerPage = 20;

    public function table(Table $table): Table
    {
        return $table
            // FIX 1: Call getAttendeeHoursQuery without arguments.
            // Filament will automatically apply the filters via the filters array below.
            ->query(fn (Builder $query) => $this->getAttendeeHoursQuery())
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Attendee')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Total Hours')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        // Ensure state is treated as a float
                        $totalMinutes = (float)$state * 60;
                        if ($totalMinutes < 1) return '0:00';

                        $hours = floor($totalMinutes / 60);
                        $minutes = round($totalMinutes % 60);

                        if ($minutes == 60) {
                            $hours++;
                            $minutes = 0;
                        }

                        // Display as H:MM
                        return $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
                    }),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    // FIX 2: Apply the date filtering logic inside the filter's query closure,
                    // where $data is correctly available.
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                // Filter meeting start time
                                fn (Builder $query, $date) => $query->whereDate('meetings.start_time', '>=', $date),
                            )
                            ->when(
                                $data['to'] ?? null,
                                // Filter meeting end time
                                fn (Builder $query, $date) => $query->whereDate('meetings.end_time', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make('table')->fromTable(),
                ])
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     //
                // ]),
            ]);
    }

    // FIX 3: Remove arguments. This method now returns the UNFILTERED base query.
    protected function getAttendeeHoursQuery(): Builder
    {
        // CRITICAL FIX (for 0:00 display): Using 60.0 to force floating-point division
        return Attendee::query()
            ->select('attendees.id', 'attendees.email')
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, meetings.start_time, meetings.end_time)) / 60.0 AS total_hours')
            ->join('attendee_meeting', 'attendees.id', '=', 'attendee_meeting.attendee_id')
            ->join('meetings', 'attendee_meeting.meeting_id', '=', 'meetings.id')
            // Removed ->when($from, ...) and ->when($to, ...) logic
            ->groupBy('attendees.id', 'attendees.email')
            ->orderByDesc('total_hours');
    }
}
