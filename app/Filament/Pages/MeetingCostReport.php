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
                    // Change to leftJoin to keep the meeting/attendee even if cost is missing
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
                        DB::raw('MAX(meetings.duration / 60) as duration_hours'),
                        // coalesce handles cases where cost is null so the sum doesn't break
                        DB::raw('SUM((meetings.duration / 60) * COALESCE(employee_cost_masters.cost_per_hour, 0)) as total_meeting_cost'),
                        // Count total participants regardless of cost availability
                        DB::raw('COUNT(attendees.id) as participant_count'),
                        // Optional: count how many actually had a cost assigned
                        DB::raw('COUNT(employee_cost_masters.email) as participants_with_cost')
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
    return DB::table('attendees')
        ->join('attendee_meeting', 'attendees.id', '=', 'attendee_meeting.attendee_id')
        ->join('meetings', 'meetings.id', '=', 'attendee_meeting.meeting_id')
        // Use leftJoin so attendees show up even if not in cost master
        ->leftJoin('employee_cost_masters', function ($join) {
            $join->on(DB::raw('LOWER(attendees.email)'), '=', DB::raw('LOWER(employee_cost_masters.email)'));
        })
        ->where('meetings.id', $meetingId)
        ->select([
            'attendees.email',
            'employee_cost_masters.cost_per_hour', // This will be NULL if no match
            DB::raw('(meetings.duration / 60) as hours'),
            // Total cost will be NULL if cost_per_hour is missing
            DB::raw('((meetings.duration / 60) * employee_cost_masters.cost_per_hour) as individual_cost'),
        ])
        ->get();
}
}
