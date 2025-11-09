<?php

namespace App\Filament\Imports;

use App\Models\Attendee;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class AttendeeImporter extends Importer
{
    protected static ?string $model = Attendee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->example('Anklesh Chauhan'),

            ImportColumn::make('email')
                ->label('Email')
                ->rules(['email'])
                ->example('anklesh.chauhan@hester.in'),
        ];
    }

    public function resolveRecord(): Attendee
    {
        return Attendee::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your attendee import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
