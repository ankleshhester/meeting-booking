<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Models\Attendee;

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
}
