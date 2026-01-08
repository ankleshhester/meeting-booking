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

class MeetingCostReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Meeting Cost Report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.pages.meeting-cost-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // We start from the meetings table
                \App\Models\Meeting::query()
                    ->join('attendee_meeting', 'meetings.id', '=', 'attendee_meeting.meeting_id')
                    ->join('attendees', 'attendees.id', '=', 'attendee_meeting.attendee_id')
                    // Join with cost master to get the hourly rate of each attendee
                    ->join('employee_cost_masters', function ($join) {
                        $join->on(DB::raw('LOWER(attendees.email)'), '=', DB::raw('LOWER(employee_cost_masters.email)'));
                    })
                    ->select([
                        'meetings.id',
                        'meetings.name as meeting_title',
                        'meetings.date as meeting_date',
                        DB::raw('MAX(meetings.duration / 60) as duration_hours'),
                        // SUM the cost of ALL attendees for this specific meeting
                        DB::raw('SUM((meetings.duration / 60) * employee_cost_masters.cost_per_hour) as total_meeting_cost'),
                        // Count participants who have a cost record
                        DB::raw('COUNT(attendees.id) as participant_count')
                    ])
                    ->groupBy('meetings.id', 'meetings.name', 'meetings.date')
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
                    ->label('View Breakdown')
                    ->icon('heroicon-m-users')
                    ->color('gray')
                    ->modalHeading(fn ($record) => "Attendee Breakdown: {$record->meeting_title}")
                    ->modalSubmitAction(false) // Remove "Submit" button as it's view-only
                    ->modalContent(fn ($record) => view('filament.pages.actions.meeting-attendees', [
                        'attendees' => $this->getAttendeeBreakdown($record->id),
                    ])),
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
        return DB::table('attendee_meeting')
            ->join('attendees', 'attendees.id', '=', 'attendee_meeting.attendee_id')
            ->join('employee_cost_masters', function ($join) {
                $join->on(DB::raw('LOWER(attendees.email)'), '=', DB::raw('LOWER(employee_cost_masters.email)'));
            })
            ->join('meetings', 'meetings.id', '=', 'attendee_meeting.meeting_id')
            ->where('meetings.id', $meetingId)
            ->select([
                'attendees.email',
                'employee_cost_masters.cost_per_hour',
                DB::raw('(meetings.duration / 60) as hours'),
                DB::raw('((meetings.duration / 60) * employee_cost_masters.cost_per_hour) as individual_cost'),
            ])
            ->get();
    }
}
