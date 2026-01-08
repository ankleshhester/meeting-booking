<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column as ExcelColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\EmployeeCostMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class EmployeeCostReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Employee Cost Report';
    // protected static ?string $navigationGroup = 'Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.pages.employee-cost-report';

    public function table(Table $table): Table
    {

       return $table
            ->query(
                EmployeeCostMaster::query()
                    ->join(
                        'attendees',
                        DB::raw('LOWER(attendees.email)'),
                        '=',
                        DB::raw('LOWER(employee_cost_masters.email)')
                    )
                    ->join('attendee_meeting', 'attendee_meeting.attendee_id', '=', 'attendees.id')
                    ->join('meetings', 'meetings.id', '=', 'attendee_meeting.meeting_id')
                    ->select([
                        // REQUIRED unique key
                        DB::raw('CONCAT(employee_cost_masters.id, "-", meetings.id) as id'),
                        'employee_cost_masters.email as employee_email',
                        'meetings.name as meeting_title',
                        'meetings.date as meeting_date',
                        DB::raw('(meetings.duration / 60) as duration_hours'),
                        DB::raw('((meetings.duration / 60) * employee_cost_masters.cost_per_hour) as total_cost'),
                    ])
            )
            ->columns([
                TextColumn::make('employee_email')
                    ->label('Employee Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('meeting_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('meeting_title')
                    ->label('Meeting Title')
                    ->searchable(),

                TextColumn::make('duration_hours')
                    ->label('Hours')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->money('INR')
                    ->sortable()
                    // If the error persists with summarize, use a closure to calculate the sum manually
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('INR')),
            ])
            ->filters([
                // Date Range Filter
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

                // Filter by Cost range
                Filter::make('total_cost')
                    ->form([
                        TextInput::make('min_cost')->numeric()->label('Min Cost'),
                        TextInput::make('max_cost')->numeric()->label('Max Cost'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_cost'], fn ($q) => $q->whereRaw('((meetings.duration / 60) * employee_cost_masters.cost_per_hour) >= ?', [$data['min_cost']]))
                            ->when($data['max_cost'], fn ($q) => $q->whereRaw('((meetings.duration / 60) * employee_cost_masters.cost_per_hour) <= ?', [$data['max_cost']]));
                    })
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Employee_Cost_Report_' . now()->format('Y-m-d'))
                            ->withColumns([
                                ExcelColumn::make('employee_email')->heading('Employee Email'),
                                ExcelColumn::make('meeting_date')->heading('Date'),
                                ExcelColumn::make('meeting_title')->heading('Meeting Title'),
                                ExcelColumn::make('duration_hours')->heading('Hours'),
                                ExcelColumn::make('total_cost')->heading('Total Cost (INR)'),
                            ]),
                    ])
                    ->label('Export Report')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
            ])
            ->defaultSort('meeting_date', 'desc')
            ->defaultSort('employee_email');
    }
}
