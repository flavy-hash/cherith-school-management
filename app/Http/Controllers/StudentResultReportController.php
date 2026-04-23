<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentResultReportController extends Controller
{
    public function show(Student $student, Request $request)
    {
        $term = $request->query('term', 'term_one');
        $year = (int) $request->query('year', now()->year);

        // Get student's results for the specified term and year
        $results = StudentResult::with(['subject'])
            ->where('student_id', $student->id)
            ->where('term', $term)
            ->where('year', $year)
            ->get();

        // Calculate statistics
        $totalSubjects = $results->count();
        $totalScore = $results->sum('score');
        $averageScore = $totalSubjects > 0 ? round($totalScore / $totalSubjects, 1) : 0;
        $passedSubjects = $results->filter(fn ($r) => $r->score >= 50)->count();
        $passRate = $totalSubjects > 0 ? round(($passedSubjects / $totalSubjects) * 100, 1) : 0;

        $pdf = Pdf::loadView('reports.student-result', [
            'student' => $student,
            'results' => $results,
            'term' => $term,
            'year' => $year,
            'averageScore' => $averageScore,
            'passRate' => $passRate,
            'passedSubjects' => $passedSubjects,
            'totalSubjects' => $totalSubjects,
            'schoolName' => 'Cherith Junior School',
            'reportDate' => now()->format('F d, Y'),
        ]);

        $filename = "student-result-{$student->admission_number}-{$term}-{$year}.pdf";

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename
        );
    }
}
