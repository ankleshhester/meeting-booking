<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use App\Models\Meeting;
use App\Services\RecurringMeetingService;
use Illuminate\Database\Eloquent\Model;

class EditMeeting extends EditRecord
{
    protected static string $resource = MeetingResource::class;

    protected bool $isRecurring = false;
    protected ?array $recurrenceData = null;

    /* =========================
     | Header Actions
     ========================= */

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelMeeting')
                ->label('Cancel Meeting')
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->canShowCancelMeetingButton())
                ->action(fn () => $this->cancelMeeting()),

            Action::make('endMeeting')
                ->label('End Meeting')
                ->icon('heroicon-o-stop-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->canShowEndMeetingButton())
                ->action(fn () => $this->endMeeting()),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /* =========================
     | Business Actions
     ========================= */

    protected function cancelMeeting(): void
    {
        $this->record->update(['status' => 'cancelled']);

        Notification::make()
            ->success()
            ->title('Meeting Cancelled')
            ->body('The meeting has been cancelled successfully.')
            ->send();

        $this->refreshFormData(['status']);
    }

    protected function endMeeting(): void
    {
        $meeting = $this->record;

        $startDateTime = Carbon::parse("{$meeting->date} {$meeting->start_time}");

        $startDateTime = Carbon::parse($meeting->date)
            ->setTimeFromTimeString($meeting->start_time);

        $now = Carbon::now();

        $actualMinutes = $startDateTime->diffInMinutes($now);

        $allowedDurations = collect(array_keys(Meeting::DURATION_SELECT))
            ->map(fn ($d) => (int) $d);

        $closestDuration = $allowedDurations
            ->sortBy(fn ($d) => abs($d - $actualMinutes))
            ->first();

        $meeting->update([
            'end_time' => $now->format('H:i'),
            'duration' => $closestDuration,
            'status'   => 'completed',
        ]);

        Notification::make()
            ->success()
            ->title('Meeting Ended')
            ->body("Meeting ended after {$actualMinutes} mins (Saved as {$closestDuration} mins).")
            ->send();

        $this->refreshFormData(['end_time', 'duration', 'status']);
    }

    /* =========================
     | Visibility Rules
     ========================= */

    protected function canShowEndMeetingButton(): bool
    {
        $meeting = $this->record;

        if (! $meeting?->date || ! $meeting?->start_time || ! $meeting?->end_time) {
            return false;
        }

        if ($meeting->status === 'completed') {
            return false;
        }

        // $start = Carbon::parse("{$meeting->date} {$meeting->start_time}");
        $start = Carbon::parse($meeting->date)
            ->setTimeFromTimeString($meeting->start_time);

        // $end   = Carbon::parse("{$meeting->date} {$meeting->end_time}");
        $end = Carbon::parse($meeting->date)
            ->setTimeFromTimeString($meeting->end_time);

        $expiry = $end->copy()->addHours(2);

        $now = Carbon::now();

        return $now->between($start, $expiry);
    }

    protected function canShowCancelMeetingButton(): bool
    {
        $meeting = $this->record;

        if (! $meeting?->date || ! $meeting?->start_time) {
            return false;
        }

        if (in_array($meeting->status, ['cancelled', 'completed'], true)) {
            return false;
        }

        // $start = Carbon::parse("{$meeting->date} {$meeting->start_time}");

        $start = Carbon::parse($meeting->date)
            ->setTimeFromTimeString($meeting->start_time);


        return Carbon::now()->lessThan($start);
    }

    /* =========================
     | Recurrence Handling
     ========================= */


    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent recurrence editing for child occurrences
        if ($this->record->parent_meeting_id) {
            unset($data['recurrence'], $data['is_recurring']);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $recurrenceData = $data['recurrence'] ?? null;
        $isRecurring    = $data['is_recurring'] ?? false;

        unset($data['recurrence'], $data['is_recurring']);

        // Update base meeting fields
        $record = parent::handleRecordUpdate($record, $data);

        // Only handle recurrence for master meetings
        if (! $record->is_master) {
            $this->sendUpdatedInvite($record);
            return $record;
        }

        /*
        |--------------------------------------------------------------------------
        | Recurrence turned OFF
        |--------------------------------------------------------------------------
        */
        if (! $isRecurring) {

            $record->recurrence()?->delete();

            Meeting::where('parent_meeting_id', $record->id)->delete();

            $this->sendUpdatedInvite($record);

            return $record;
        }

        /*
        |--------------------------------------------------------------------------
        | Recurrence turned ON / updated
        |--------------------------------------------------------------------------
        */

        if ($recurrenceData) {
            $record->recurrence()->updateOrCreate([], $recurrenceData);
        }

        // Delete ALL occurrences
        Meeting::where('parent_meeting_id', $record->id)->delete();

        // Regenerate entire series
        app(RecurringMeetingService::class)->generate($record, true);

        $this->sendUpdatedInvite($record);

        return $record;
    }

    protected function sendUpdatedInvite(Meeting $meeting): void
    {
        $recipientEmails = $meeting->addAttendee()
            ->pluck('email')
            ->filter()
            ->unique()
            ->toArray();

        if (! empty($recipientEmails)) {
            \Mail::to($recipientEmails)
                ->send(new \App\Mail\MeetingInviteWithICS($meeting));
        }
    }


}
