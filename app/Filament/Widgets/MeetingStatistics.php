<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Meeting;
use Carbon\Carbon;

class MeetingStatistics extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Total meeting hours
        $totalMinutes = Meeting::get()->sum(function ($meeting) {
            if (!$meeting->start_time || !$meeting->end_time) return 0;
            return Carbon::parse($meeting->start_time)->diffInMinutes(Carbon::parse($meeting->end_time));
        });
        $totalHours = round($totalMinutes / 60, 2);

        // Conference room used hours
        $roomMinutes = Meeting::whereNotNull('rooms_id')->get()->sum(function ($meeting) {
            if (!$meeting->start_time || !$meeting->end_time) return 0;
            return Carbon::parse($meeting->start_time)->diffInMinutes(Carbon::parse($meeting->end_time));
        });
        $roomHours = round($roomMinutes / 60, 2);

        return [
            Stat::make('Total Meeting Hours', $totalHours . ' hrs'),
            Stat::make('Conference Room Used', $roomHours . ' hrs'),
        ];
    }
}
