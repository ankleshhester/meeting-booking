<?php

namespace App\Filament\Imports;

use App\Models\EmployeeCostMaster;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmployeeCostMasterImporter extends Importer
{
    protected static ?string $model = EmployeeCostMaster::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('emp_code')
                ->label('Employee Code')
                ->rules(['required', 'max:255'])
                ->example('EMP001'),

            ImportColumn::make('email')
                ->label('Email')
                ->rules(['required', 'email'])
                ->example('employee@example.com'),

            ImportColumn::make('ctc')
                ->label('CTC')
                ->rules(['required', 'numeric', 'min:0'])
                ->example('500.00'),
        ];
    }

    public function resolveRecord(): EmployeeCostMaster
    {
        // Use email as the unique identifier to update existing records or create new ones
        return EmployeeCostMaster::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee cost import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
