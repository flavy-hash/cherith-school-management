<?php

namespace App\Filament\Exports;

use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PaymentExporter extends Exporter
{
    protected static ?string $model = Payment::class;
    protected static ?string $fileDisk = 'public';
    protected static ?string $queue = 'sync';

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('student.full_name')
                ->label('Student Name'),
            
            ExportColumn::make('student.admission_number')
                ->label('Admission Number'),
            
            ExportColumn::make('student.standard.name')
                ->label('Class'),
            
            ExportColumn::make('term')
                ->label('Term'),
            
            ExportColumn::make('year'),
            
            ExportColumn::make('amount')
                ->label('Amount Paid'),
            
            ExportColumn::make('balance')
                ->label('Remaining Balance'),
            
            ExportColumn::make('payment_method')
                ->label('Payment Method'),
            
            ExportColumn::make('is_verified')
                ->label('Verified')
                ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),
            
            ExportColumn::make('payment_date')
                ->label('Payment Date'),
            
            ExportColumn::make('created_at')
                ->label('Recorded At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payment export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
