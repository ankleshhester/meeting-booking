<?php

namespace App\Filament\Resources\BuildingsAreas\Pages;

use App\Filament\Resources\BuildingsAreas\BuildingsAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBuildingsAreas extends ListRecords
{
    protected static string $resource = BuildingsAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
