<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Exports\PaymentExporter;
use App\Filament\Resources\PaymentResource;
use App\Imports\PaymentsImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(PaymentExporter::class),
            Action::make('import_payments_excel')
                ->label('Import Payments (Excel)')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->maxFiles(1),
                ])
                ->action(function (array $data): void {
                    $import = new PaymentsImport();

                    $file = $data['file'] ?? null;
                    if (is_array($file)) {
                        $file = $file[0] ?? null;
                    }

                    $path = null;
                    if ($file instanceof TemporaryUploadedFile) {
                        $path = $file->store('imports', 'local');
                    } elseif (is_string($file) && $file !== '') {
                        $path = $file;
                    }

                    if (! $path) {
                        Notification::make()
                            ->title('Payments Import Failed')
                            ->body('No file was uploaded.')
                            ->danger()
                            ->send();
                        return;
                    }

                    Excel::import($import, $path, 'local');

                    $errorsCount = count($import->rowErrors);
                    $firstErrors = collect($import->rowErrors)
                        ->take(5)
                        ->map(fn (array $e) => 'Row ' . $e['row'] . ': ' . implode('; ', $e['errors']))
                        ->implode("\n");

                    $body = "Created: {$import->created}\nUpdated: {$import->updated}\nSkipped: {$import->skipped}\nErrors: {$errorsCount}";
                    if ($errorsCount > 0 && $firstErrors !== '') {
                        $body .= "\n\n" . $firstErrors;
                    }

                    Notification::make()
                        ->title('Payments Import Finished')
                        ->body($body)
                        ->color($errorsCount > 0 ? 'warning' : 'success')
                        ->send();
                }),

            Action::make('show_tra_sent_payments')
                ->label('TRA Sent Payments')
                ->icon('heroicon-o-funnel')
                ->color('info')
                ->url(fn () => PaymentResource::getUrl('index', ['tableFilters' => ['tra_status' => ['value' => 'sent']]])),

            Action::make('print_all_receipts_smartefd')
                ->label('Print All Receipts (SmartEFD)')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('filament.admin.payments.receipts.bulk', request()->query()))
                ->openUrlInNewTab(),

            Action::make('print_all_receipts_smartefd_80mm')
                ->label('Print All Receipts (80mm)')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('filament.admin.payments.receipts.bulk', array_merge(request()->query(), ['format' => '80mm'])))
                ->openUrlInNewTab(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $studentId = request()->query('student_id');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        return $query;
    }
}
