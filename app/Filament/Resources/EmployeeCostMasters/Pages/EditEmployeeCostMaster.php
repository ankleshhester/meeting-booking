<?php

namespace App\Filament\Resources\EmployeeCostMasters\Pages;

use App\Filament\Resources\EmployeeCostMasters\EmployeeCostMasterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeCostMaster extends EditRecord
{
    protected static string $resource = EmployeeCostMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
