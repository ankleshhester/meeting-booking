<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use App\Models\Meeting;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalMeetingHours extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalMinutes = Meeting::get()
            ->sum(function ($meeting) {
                if (!$meeting->start_time || !$meeting->end_time) {
                    return 0;
                }

                return Carbon::parse($meeting->start_time)
                    ->diffInMinutes(Carbon::parse($meeting->end_time));
            });

        $hours = round($totalMinutes / 60, 2);

        return [
            Stat::make('Total Meeting Hours', $hours . ' hrs')
                ->description('Calculated using start & end time'),
        ];
    }
}
