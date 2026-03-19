<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class StudentDebtReport extends Page
{
    protected static string $resource = StudentResource::class;
    protected string $view = 'filament.resources.student-resource.pages.student-debt-report';

    public $students;
    public $totalDebt = 0;
    public $reportDate;

    public function mount()
    {
        $year = (int) date('Y');

        $this->students = Student::with([
            'standard',
            'payments' => fn ($query) => $query->where('year', $year),
        ])
            ->get()
            ->filter(fn ($student) => $student->total_debt > 0);

        $this->totalDebt = $this->students->sum('total_debt');
        $this->reportDate = now()->format('Y-m-d');
    }

    protected function getActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action('exportPDF'),

            Action::make('back')
                ->label('Back to Students')
                ->url(StudentResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function exportPDF()
    {
        $pdf = Pdf::loadView('reports.student-debt', [
            'students' => $this->students,
            'totalDebt' => $this->totalDebt,
            'reportDate' => $this->reportDate,
            'schoolName' => 'Cherith Junior School',
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "student-debt-report-{$this->reportDate}.pdf"
        );
    }
}
