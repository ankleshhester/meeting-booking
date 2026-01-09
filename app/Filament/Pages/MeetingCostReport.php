<?php

namespace App\Filament\Pages;

use App\Models\EmployeeCostMaster;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column as ExcelColumn;
use Filament\Actions\Action;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;

class MeetingCostReport extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:MeetingCostReport');
    }

    protected static ?string $navigationLabel = 'Meeting Cost Report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.pages.meeting-cost-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Meeting::query()
                    ->join('attendee_meeting', 'meetings.id', '=', 'attendee_meeting.meeting_id')
                    ->join('attendees', 'attendees.id', '=', 'attendee_meeting.attendee_id')

                    // Keep meeting/attendee even if cost is missing
                    ->leftJoin('employee_cost_masters', function ($join) {
                        $join->on(
                            DB::raw('LOWER(attendees.email)'),
                            '=',
                            DB::raw('LOWER(employee_cost_masters.email)')
                        );
                    })

                    ->select([
                        'meetings.id',
                        'meetings.name as meeting_title',
                        'meetings.date as meeting_date',

                        // duration in hours (same for all rows of the meeting)
                        DB::raw('MAX(meetings.duration / 60) as duration_hours'),

                        // ✅ TOTAL MEETING COST (CTC / 2500 × hours × attendees)
                        DB::raw('
                            SUM(
                                (meetings.duration / 60)
                                * COALESCE(employee_cost_masters.ctc, 0)
                                / 2500
                            ) as total_meeting_cost
                        '),

                        // total participants
                        DB::raw('COUNT(attendees.id) as participant_count'),

                        // participants having CTC defined
                        DB::raw('COUNT(employee_cost_masters.email) as participants_with_cost'),
                    ])
                    ->groupBy(
                        'meetings.id',
                        'meetings.name',
                        'meetings.date'
                    )
            )
            ->columns([
                TextColumn::make('meeting_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('meeting_title')
                    ->label('Meeting Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant_count')
                    ->label('Participants')
                    ->badge()
                    ->color('info'),

                TextColumn::make('duration_hours')
                    ->label('Duration (Hrs)')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('total_meeting_cost')
                    ->label('Total Meeting Cost')
                    ->money('INR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('INR')),
            ])
            ->actions([
                Action::make('view_attendees')
                    ->label('View Details')
                    ->icon('heroicon-m-users')
                    ->color('gray')
                    ->modalHeading(fn ($record) => "Attendee Breakdown: {$record->meeting_title}")
                    ->modalSubmitAction(false) // Remove "Submit" button as it's view-only
                    ->modalContent(fn ($record) => view('filament.pages.actions.meeting-attendees', [
                        'attendees' => $this->getAttendeeBreakdown($record->id),
                    ])),
            ])
            ->filters([
                Filter::make('meeting_date')
                    ->form([
                        DatePicker::make('from')->label('From Date'),
                        DatePicker::make('until')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('meetings.date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('meetings.date', '<=', $date),
                            );
                    }),

            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Meeting_Cost_Report_' . now()->format('Y-m-d'))
                            ->withColumns([
                                ExcelColumn::make('meeting_date')->heading('Date'),
                                ExcelColumn::make('meeting_title')->heading('Meeting Title'),
                                ExcelColumn::make('participant_count')->heading('Participants'),
                                ExcelColumn::make('duration_hours')->heading('Hours'),
                                ExcelColumn::make('total_meeting_cost')->heading('Total Cost'),
                            ]),
                    ])
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
            ])
            ->defaultSort('meeting_date', 'desc');
    }

    public function getAttendeeBreakdown($meetingId)
    {
        return DB::table('attendees')
            ->join('attendee_meeting', 'attendees.id', '=', 'attendee_meeting.attendee_id')
            ->join('meetings', 'meetings.id', '=', 'attendee_meeting.meeting_id')

            // Keep attendee even if no cost master entry exists
            ->leftJoin('employee_cost_masters', function ($join) {
                $join->on(
                    DB::raw('LOWER(attendees.email)'),
                    '=',
                    DB::raw('LOWER(employee_cost_masters.email)')
                );
            })

            ->where('meetings.id', $meetingId)

            ->select([
                'attendees.email',

                // Raw CTC (optional, useful for debugging / display)
                'employee_cost_masters.ctc as ctc',

                // Meeting duration in hours
                DB::raw('(meetings.duration / 60) as hours'),

                // Hourly cost derived from CTC
                DB::raw('(employee_cost_masters.ctc / 2500) as hourly_cost'),

                // ✅ Individual attendee cost (hours × hourly cost)
                DB::raw('
                    (meetings.duration / 60)
                    * (employee_cost_masters.ctc / 2500)
                    as individual_cost
                '),
            ])
            ->get();
    }

}
