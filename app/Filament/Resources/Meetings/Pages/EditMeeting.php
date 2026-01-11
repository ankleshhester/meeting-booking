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

class EditMeeting extends EditRecord
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('endMeeting')
                ->label('End Meeting')
                ->icon('heroicon-o-stop-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->canShowEndMeetingButton())
                ->action(function () {
                    $this->record->update([
                        'end_time' => Carbon::now()->format('H:i'),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Meeting Ended')
                        ->body('The meeting has been ended successfully.')
                        ->send();

                    $this->refreshFormData(['end_time']);
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

        $startDateTime = Carbon::parse("{$meeting->date} {$meeting->start_time}");
        $endDateTime   = Carbon::parse("{$meeting->date} {$meeting->end_time}");
        $expiryTime    = $endDateTime->copy()->addHours(4);

        return $now->greaterThanOrEqualTo($startDateTime)
            && $now->lessThanOrEqualTo($expiryTime);
    }
}
