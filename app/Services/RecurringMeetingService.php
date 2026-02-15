<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingRecurrence;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecurringMeetingService
{
    /**
     * Generate recurring meeting instances
     */
    public function generate(Meeting $masterMeeting, bool $regenerateFuture = false): Collection
    {
        $recurrence = $masterMeeting->recurrence;

        if (! $recurrence) {
            return collect();
        }

        return DB::transaction(function () use ($masterMeeting, $recurrence, $regenerateFuture) {

            // Ensure master is marked correctly
            $masterMeeting->update([
                'is_master' => true,
                'parent_meeting_id' => null,
            ]);

            // 🔁 If regenerating, delete future occurrences first
            if ($regenerateFuture) {
                Meeting::where('parent_meeting_id', $masterMeeting->id)
                    ->delete();
            }

            $dates = $this->buildOccurrenceDates($masterMeeting, $recurrence);

            $createdMeetings = collect();

            foreach ($dates as $date) {

                // Skip master date
                if ($date->isSameDay(Carbon::parse($masterMeeting->date))) {
                    continue;
                }

                // Skip if already exists (extra safety)
                $alreadyExists = Meeting::where('parent_meeting_id', $masterMeeting->id)
                    ->whereDate('date', $date)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                // Optional room availability check
                if (! $this->roomIsAvailable($masterMeeting, $date)) {
                    continue;
                }

                $newMeeting = $masterMeeting->replicate();

                $newMeeting->date = $date;
                $newMeeting->is_master = false;
                $newMeeting->parent_meeting_id = $masterMeeting->id;
                $newMeeting->status = 'scheduled';

                $newMeeting->save();

                // Attach attendees
                $newMeeting->addAttendee()->sync(
                    $masterMeeting->addAttendee->pluck('id')->toArray()
                );

                $createdMeetings->push($newMeeting);
            }

            return $createdMeetings;
        });
    }

    /**
     * Build recurrence dates
     */
    protected function buildOccurrenceDates(
        Meeting $meeting,
        MeetingRecurrence $recurrence
    ): Collection {

        $dates = collect();

        $startDate = Carbon::parse($recurrence->start_date ?? $meeting->date)->startOfDay();
        $current   = $startDate->copy();

        $endDate = $recurrence->end_date
            ? Carbon::parse($recurrence->end_date)->endOfDay()
            : null;

        $limit    = $recurrence->occurrences ?? 50;
        $interval = max(1, (int) $recurrence->interval);

        $baseWeekStart = $startDate->copy()->startOfWeek();

        while ($dates->count() < $limit) {

            if ($endDate && $current->gt($endDate)) {
                break;
            }

            if ($this->matchesRule($current, $recurrence)) {

                // Weekly interval logic
                if ($recurrence->frequency === 'weekly') {

                    $weeksDiff = $baseWeekStart->diffInWeeks(
                        $current->copy()->startOfWeek()
                    );

                    if ($weeksDiff % $interval !== 0) {
                        $current->addDay();
                        continue;
                    }
                }

                $dates->push($current->copy());
            }

            // Move pointer
            $current = match ($recurrence->frequency) {
                'daily'   => $current->addDays($interval),
                'monthly' => $current->addMonths($interval),
                default   => $current->addDay(),
            };
        }

        return $dates;
    }

    /**
     * Check weekly matching rule
     */
    protected function matchesRule(Carbon $date, MeetingRecurrence $recurrence): bool
    {
        if ($recurrence->frequency !== 'weekly') {
            return true;
        }

        if (! $recurrence->days_of_week) {
            return true;
        }

        return in_array(
            strtolower($date->format('D')),
            $recurrence->days_of_week
        );
    }

    /**
     * Room availability check
     */
    protected function roomIsAvailable(Meeting $meeting, Carbon $date): bool
    {
        return ! Meeting::query()
            ->where('rooms_id', $meeting->rooms_id)
            ->whereDate('date', $date)
            ->where(function ($q) use ($meeting) {
                $q->whereTime('start_time', '<', $meeting->end_time)
                  ->whereTime('end_time', '>', $meeting->start_time);
            })
            ->exists();
    }
}
