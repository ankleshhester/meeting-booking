<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Models\Attendee;
use App\Models\MeetingMinute;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingInviteWithICS;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CreateMeeting extends CreateRecord
{
    protected static string $resource = MeetingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If add_attendee exists and is a list of emails, validate & convert to IDs
        if (! empty($data['add_attendee']) && is_array($data['add_attendee'])) {
            $emails = array_values(array_map('trim', $data['add_attendee']));

            // Validate each email strictly
            $invalid = array_filter($emails, fn($e) => ! filter_var($e, FILTER_VALIDATE_EMAIL));
            if (! empty($invalid)) {
                throw ValidationException::withMessages([
                    'add_attendee' => ['Invalid email(s): ' . implode(', ', $invalid)],
                ]);
            }

            // Ensure attendees exist and map to IDs
            foreach ($emails as $email) {
                Attendee::firstOrCreate(['email' => $email]);
            }

            $data['add_attendee'] = Attendee::whereIn('email', $emails)->pluck('id')->toArray();
        }

        return $data;
    }

    // Use the non-static, protected method signature for the Page hook
    protected function afterCreate(): void
    {
        $meeting = $this->record; // $this->record holds the created Meeting model
        $formData = $this->data;   // $this->data holds the form input values

        Log::info('*** CreateMeeting afterCreate Hook Triggered ***');
        // Log::info('Form Data: ' . print_r($formData, true)); // You can log the form data here


        // 3. Collect and sync attendees (including host)
        $attendeeIds = array_map('intval', array_values($formData['add_attendee'] ?? []));

        // Add Host logic
        if ($meeting->created_by_id && $meeting->host) {
            $hostAttendee = Attendee::firstOrCreate(
                ['email' => $meeting->host->email],
                ['name' => $meeting->host->name]
            );
            $attendeeIds[] = $hostAttendee->id;
        }

        $attendeeIds = array_unique($attendeeIds);

        if (!empty($attendeeIds)) {
            $meeting->addAttendee()->sync($attendeeIds);

            // 4. Send emails
            $recipientEmails = Attendee::whereIn('id', $attendeeIds)
                ->pluck('email')
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($recipientEmails)) {
                Mail::to($recipientEmails)->send(new MeetingInviteWithICS($meeting));
            }
        }
    }
}
