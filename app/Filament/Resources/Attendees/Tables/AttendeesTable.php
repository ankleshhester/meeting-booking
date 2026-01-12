<?php

namespace App\Filament\Resources\Attendees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\ImportAction;
use App\Filament\Imports\AttendeeImporter;
use Dom\Text;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column as ExcelColumn;

class AttendeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('emp_code')
                    ->label('Employee Code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('comment')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),

                ImportAction::make()
                    ->label('Import Attendees')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->importer(AttendeeImporter::class),

                // ✅ EXPORT ACTION
                ExportAction::make()
                    ->label('Export Attendees')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable() // respects filters, search, sorting
                            ->withFilename('attendees_' . now()->format('Y-m-d'))
                            ->withColumns([
                                ExcelColumn::make('emp_code')->heading('Employee Code'),
                                ExcelColumn::make('email')->heading('Email'),
                                ExcelColumn::make('name')->heading('Name'),
                                ExcelColumn::make('comment')->heading('Comment'),
                            ]),
                    ]),
            ]);
    }
}
