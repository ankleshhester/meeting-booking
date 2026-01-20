<?php

namespace App\Filament\Widgets;

use App\Models\ConferenceRoom;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ConferenceRoomAvailability extends TableWidget
{
    protected static ?string $heading = 'Conference Room Booking';
    protected static ?int $sort = 1;

    protected function getHeading(): string
    {
        $dateFilter = $this->getTableFilterState('date');
        $date = $dateFilter['date'] ?? today()->toDateString();

        return 'Conference Room Booking — ' . Carbon::parse($date)->format('d M Y');
    }

    public function table(Table $table): Table
    {
        return $table
            // 🔒 Always load ALL rooms
            ->query(fn () => ConferenceRoom::query())

            // 🎯 Filters
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->default(today()),
                    ]),
            ])

            ->modifyQueryUsing(function ($query) {
                $dateFilter = $this->getTableFilterState('date');
                $date = $dateFilter['date'] ?? today()->toDateString();

                $query->with([
                    'meetings' => function ($q) use ($date) {
                        $q->whereDate('date', $date)
                        ->where(function ($q) {
                            $q->whereNull('status')
                                ->orWhere('status', '!=', 'cancelled');
                        })
                        ->where('start_time', '<', '17:30:00')
                        ->where('end_time', '>', '09:00:00')
                        ->orderBy('start_time');
                    },
                ]);
            })

            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Conference Room')
                    ->searchable(),

                Tables\Columns\TextColumn::make('booked_slots')
                    ->label('Meeting Slots - Booked')
                    ->badge()
                    ->listWithLineBreaks()
                    ->getStateUsing(function ($record) {
                        if ($record->meetings->isEmpty()) {
                            return ['—'];
                        }

                        return $record->meetings
                            ->map(function ($m) {
                                $time = \Carbon\Carbon::parse($m->start_time)->format('H:i')
                                    . ' - ' .
                                    \Carbon\Carbon::parse($m->end_time)->format('H:i');

                                $name = optional($m->organizer)->name ?? 'Unknown';

                                return "{$time} by {$name}";
                            })
                            ->values()
                            ->toArray();
                    })
                    ->wrap(),

                // ✅ NEW: Available Time column
                Tables\Columns\TextColumn::make('available_slots')
                    ->label('Meeting Slots - Available')
                    ->badge()
                    ->listWithLineBreaks()
                    ->getStateUsing(function ($record) {
                        $dayStart = Carbon::createFromTime(9, 0);
                        $dayEnd   = Carbon::createFromTime(17, 30);

                        if ($record->meetings->isEmpty()) {
                            return [
                                $dayStart->format('H:i') . ' - ' . $dayEnd->format('H:i'),
                            ];
                        }

                        $slots = [];
                        $current = $dayStart;

                        foreach ($record->meetings as $meeting) {
                            $start = Carbon::parse($meeting->start_time);
                            $end   = Carbon::parse($meeting->end_time);

                            if ($current->lt($start)) {
                                $slots[] = $current->format('H:i') . ' - ' . $start->format('H:i');
                            }

                            $current = max($current, $end);
                        }

                        if ($current->lt($dayEnd)) {
                            $slots[] = $current->format('H:i') . ' - ' . $dayEnd->format('H:i');
                        }

                        return $slots;
                    })
                    ->separator(',') // optional
                    ->wrap(),

            ]);
        }


}
