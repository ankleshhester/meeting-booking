<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Models\Attendee;
use App\Models\Meeting;
use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingInviteWithICS;
use App\Services\RecurringMeetingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class CreateMeeting extends CreateRecord
{
    protected static string $resource = MeetingResource::class;

    /**
     * Normalize attendees before create
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['add_attendee']) && is_array($data['add_attendee'])) {
            $emails = collect($data['add_attendee'])
                ->map(fn ($e) => trim($e))
                ->unique()
                ->values();

            $invalid = $emails->filter(
                fn ($email) => ! filter_var($email, FILTER_VALIDATE_EMAIL)
            );

            if ($invalid->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'add_attendee' => [
                        'Invalid email(s): ' . $invalid->implode(', ')
                    ],
                ]);
            }

            // Ensure attendees exist
            $emails->each(fn ($email) =>
                Attendee::firstOrCreate(['email' => $email])
            );

            // Replace emails with IDs
            $data['add_attendee'] = Attendee::whereIn('email', $emails)
                ->pluck('id')
                ->toArray();
        }

        $isRecurring = $data['is_recurring'] ?? false;

        // Ensure master flag is saved correctly
        $data['is_master'] = $isRecurring ? true : false;

        return $data;
    }

    /**
     * Create meeting + recurrence
     */
    protected function handleRecordCreation(array $data): Model
    {
        $recurrenceData = $data['recurrence'] ?? null;
        $isRecurring    = $data['is_recurring'] ?? false;

        unset($data['recurrence'], $data['is_recurring']);

        /** @var Meeting $meeting */
        $meeting = parent::handleRecordCreation($data);

        $meeting->forceFill([
            'is_master' => $isRecurring ? true : false,
            'parent_meeting_id' => null,
        ])->save();

        if ($isRecurring && $recurrenceData) {

            // Safety: prevent infinite recurrence
            if (
                empty($recurrenceData['end_date']) &&
                empty($recurrenceData['occurrences'])
            ) {
                $recurrenceData['occurrences'] = 50;
            }

            $meeting->recurrence()->create([
                ...$recurrenceData,
                'start_date' => $recurrenceData['start_date'] ?? $meeting->date,
            ]);

            app(RecurringMeetingService::class)->generate($meeting);
        }

        return $meeting;
    }

    /**
     * After everything is created
     */
    protected function afterCreate(): void
    {
        /** @var Meeting $meeting */
        $meeting  = $this->record;
        $formData = $this->data;

        Log::info('Meeting created', ['meeting_id' => $meeting->id]);

        $attendeeIds = collect($formData['add_attendee'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        // Always include host
        if ($meeting->created_by_id && $meeting->host) {
            $hostAttendee = Attendee::firstOrCreate(
                ['email' => $meeting->host->email],
                ['name' => $meeting->host->name]
            );

            $attendeeIds->push($hostAttendee->id);
        }

        if ($attendeeIds->isEmpty()) {
            return;
        }

        // Sync attendees
        $meeting->addAttendee()->sync($attendeeIds->toArray());

        // Send invites ONLY for master meeting
        $recipientEmails = Attendee::whereIn('id', $attendeeIds)
            ->pluck('email')
            ->filter()
            ->unique()
            ->toArray();

        if (! empty($recipientEmails)) {
            Mail::to($recipientEmails)
                ->send(new MeetingInviteWithICS($meeting, 'create'));
        }
    }
}
