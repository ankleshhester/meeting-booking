<?php

namespace App\Filament\Resources\BuildingsAreas\Pages;

use App\Filament\Resources\BuildingsAreas\BuildingsAreaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBuildingsArea extends EditRecord
{
    protected static string $resource = BuildingsAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
