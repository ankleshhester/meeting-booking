<?php

namespace App\Filament\Resources\ConferenceRooms\Pages;

use App\Filament\Resources\ConferenceRooms\ConferenceRoomResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditConferenceRoom extends EditRecord
{
    protected static string $resource = ConferenceRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
