<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Carbon\Carbon;
use App\Models\Meeting;

class EditMeeting extends EditRecord
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelMeeting')
            ->label('Cancel Meeting')
            ->icon('heroicon-o-x-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn () => $this->canShowCancelMeetingButton())
            ->action(function () {

                $this->record->update([
                    'status' => 'cancelled',
                ]);

                Notification::make()
                    ->success()
                    ->title('Meeting Cancelled')
                    ->body('The meeting has been cancelled successfully.')
                    ->send();

                $this->refreshFormData(['status']);
            }),

            Action::make('endMeeting')
                ->label('End Meeting')
                ->icon('heroicon-o-stop-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->canShowEndMeetingButton())
                ->action(function () {

                    $meeting = $this->record;

                    // Normalize start time
                    $startTime = Carbon::parse($meeting->start_time)->format('H:i:s');
                    $startDateTime = Carbon::parse("{$meeting->date} {$startTime}");

                    $now = Carbon::now();

                    // 🧮 Actual duration in minutes
                    $actualMinutes = $startDateTime->diffInMinutes($now);

                    // 🎯 Pick closest allowed duration
                    $allowedDurations = array_keys(Meeting::DURATION_SELECT);

                    $closestDuration = collect($allowedDurations)
                        ->map(fn ($d) => (int) $d)
                        ->sortBy(fn ($d) => abs($d - $actualMinutes))
                        ->first();

                    // 🕒 Final end time based on actual click time
                    $meeting->update([
                        'end_time' => $now->format('H:i'),
                        'duration' => $closestDuration,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Meeting Ended')
                        ->body("Meeting ended after {$actualMinutes} mins (Saved as {$closestDuration} mins).")
                        ->send();

                    $this->refreshFormData(['end_time', 'duration']);
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Visibility logic for End Meeting button
     */
    protected function canShowEndMeetingButton(): bool
    {
        $meeting = $this->record;

        if (! $meeting?->date || ! $meeting?->start_time || ! $meeting?->end_time) {
            return false;
        }

        $now = Carbon::now();

        $startTime = Carbon::parse($meeting->start_time)->format('H:i:s');
        $endTime   = Carbon::parse($meeting->end_time)->format('H:i:s');

        $startDateTime = Carbon::parse("{$meeting->date} {$startTime}");
        $endDateTime   = Carbon::parse("{$meeting->date} {$endTime}");
        $expiryTime    = $endDateTime->copy()->addHours(2);

        return $now->greaterThanOrEqualTo($startDateTime)
            && $now->lessThanOrEqualTo($expiryTime);
    }

    protected function canShowCancelMeetingButton(): bool
    {
        $meeting = $this->record;

        if (! $meeting?->date || ! $meeting?->start_time) {
            return false;
        }

        // ❌ Do not show if already cancelled or completed
        if (in_array($meeting->status, ['cancelled', 'completed'], true)) {
            return false;
        }

        $startTime = Carbon::parse($meeting->start_time)->format('H:i:s');
        $startDateTime = Carbon::parse("{$meeting->date} {$startTime}");

        return Carbon::now()->lessThan($startDateTime);
    }

}
