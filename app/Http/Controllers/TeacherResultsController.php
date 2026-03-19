<?php

namespace App\Http\Controllers;

use App\Models\Standard;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\TeacherSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherResultsController extends Controller
{
    public function index(Request $request): View
    {
        /** @var TeacherSubject $teacherSubject */
        $teacherSubject = $request->attributes->get('teacherSubject');

        $standardId = $request->query('standard_id');
        $term = $request->query('term', 'term_one');
        $year = (int) $request->query('year', date('Y'));

        $standards = Standard::query()->orderBy('name')->get();

        $students = collect();
        if ($standardId) {
            $students = Student::query()
                ->where('standard_id', $standardId)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        }

        $existing = [];
        if ($standardId) {
            $existing = StudentResult::query()
                ->where('subject_id', $teacherSubject->subject_id)
                ->where('term', $term)
                ->where('year', $year)
                ->whereIn('student_id', $students->pluck('id'))
                ->pluck('score', 'student_id')
                ->all();
        }

        return view('teacher.results', [
            'teacherSubject' => $teacherSubject->loadMissing('subject'),
            'standards' => $standards,
            'students' => $students,
            'existing' => $existing,
            'selectedStandardId' => $standardId ? (int) $standardId : null,
            'term' => $term,
            'year' => $year,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var TeacherSubject $teacherSubject */
        $teacherSubject = $request->attributes->get('teacherSubject');

        $validated = $request->validate([
            'standard_id' => ['required', 'integer', 'exists:standards,id'],
            'term' => ['required', 'in:term_one,term_two'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $standardId = (int) $validated['standard_id'];
        $term = (string) $validated['term'];
        $year = (int) $validated['year'];
        $scores = $validated['scores'];

        $students = Student::query()
            ->where('standard_id', $standardId)
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => (int) $id])
            ->all();

        foreach ($scores as $studentId => $score) {
            if (! array_key_exists((string) $studentId, $students)) {
                continue;
            }

            if ($score === null || $score === '') {
                continue;
            }

            StudentResult::updateOrCreate(
                [
                    'student_id' => (int) $studentId,
                    'subject_id' => $teacherSubject->subject_id,
                    'term' => $term,
                    'year' => $year,
                ],
                [
                    'teacher_id' => $request->user()->id,
                    'score' => (int) $score,
                ],
            );
        }

        return redirect()
            ->route('teacher.results.index', [
                'standard_id' => $standardId,
                'term' => $term,
                'year' => $year,
            ])
            ->with('status', 'Results saved successfully.');
    }
}
