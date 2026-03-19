<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;
    protected static ?string $fileDisk = 'public';
    protected static ?string $queue = 'sync';

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('admission_number'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('full_name'),
            ExportColumn::make('standard.name')
                ->label('Class'),
            ExportColumn::make('gender'),
            ExportColumn::make('date_of_birth'),
            ExportColumn::make('parent_name'),
            ExportColumn::make('parent_phone'),
            ExportColumn::make('parent_email'),
            ExportColumn::make('address'),
            ExportColumn::make('term_one_balance')
                ->label('Term 1 Balance'),
            ExportColumn::make('term_two_balance')
                ->label('Term 2 Balance'),
            ExportColumn::make('total_debt')
                ->label('Total Debt'),
            ExportColumn::make('status'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
