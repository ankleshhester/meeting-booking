<?php

namespace App\Filament\Resources\EmployeeCostMasters\Pages;

use App\Filament\Resources\EmployeeCostMasters\EmployeeCostMasterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeCostMasters extends ListRecords
{
    protected static string $resource = EmployeeCostMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
