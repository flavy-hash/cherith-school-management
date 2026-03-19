<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Imports\StudentsImport;
use App\Filament\Exports\StudentExporter;
use App\Filament\Resources\StudentResource;
use Filament\Forms;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(StudentExporter::class),
            Action::make('import_students_excel')
                ->label('Import Students (Excel)')
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
                    $import = new StudentsImport();

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
                            ->title('Students Import Failed')
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
                        ->title('Students Import Finished')
                        ->body($body)
                        ->color($errorsCount > 0 ? 'warning' : 'success')
                        ->send();
                }),
            Action::make('debt_report')
                ->label('Debt Report')
                ->icon('heroicon-o-document-text')
                ->url(StudentResource::getUrl('debt-report')),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $standardId = request()->query('standard_id');

        if ($standardId) {
            $query->where('standard_id', $standardId);
        }

        return $query;
    }
}
