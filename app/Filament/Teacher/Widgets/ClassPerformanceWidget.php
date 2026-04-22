<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\StudentResult;
use App\Models\TeacherSubject;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClassPerformanceWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();
        if (! $userId) {
            return [];
        }

        $year = (int) now()->year;
        $term = now()->month <= 6 ? 'term_one' : 'term_two';

        $assignments = TeacherSubject::query()
            ->where('user_id', $userId)
            ->whereNotNull('standard_id')
            ->with(['standard'])
            ->get()
            ->groupBy('standard_id');

        $standardIds = $assignments->keys()->filter()->values();
        if ($standardIds->isEmpty()) {
            return [];
        }

        $rows = StudentResult::query()
            ->join('students', 'student_results.student_id', '=', 'students.id')
            ->whereIn('students.standard_id', $standardIds->all())
            ->where('student_results.year', $year)
            ->where('student_results.term', $term)
            ->select([
                'students.standard_id as standard_id',
                DB::raw('COUNT(DISTINCT student_results.student_id) as students_assessed'),
                DB::raw('COUNT(*) as results_count'),
                DB::raw('AVG(student_results.score) as avg_score'),
                DB::raw('SUM(CASE WHEN student_results.score >= 50 THEN 1 ELSE 0 END) as passes'),
            ])
            ->groupBy('students.standard_id')
            ->get()
            ->keyBy('standard_id');

        return $assignments
            ->map(function (Collection $group, $standardId) use ($rows) {
                $standard = $group->first()?->standard;
                $assignmentId = (int) ($group->sortBy('id')->first()?->id ?? 0);
                $row = $rows->get($standardId);

                $resultsCount = (int) ($row->results_count ?? 0);
                $passes = (int) ($row->passes ?? 0);
                $passRate = $resultsCount > 0 ? round(($passes / $resultsCount) * 100, 1) : null;
                $avg = $row?->avg_score !== null ? round((float) $row->avg_score, 1) : null;

                $standardName = $standard?->name ?? ('Class ' . $standardId);

                $description = 'Students: ' . (int) ($row->students_assessed ?? 0)
                    . ' | Results: ' . $resultsCount
                    . ' | Pass: ' . ($passRate !== null ? ($passRate . '%') : '—');

                $color = $passRate === null
                    ? 'gray'
                    : ($passRate >= 75 ? 'success' : ($passRate >= 50 ? 'warning' : 'danger'));

                return Stat::make($standardName, $avg !== null ? (string) $avg : '—')
                    ->description($description)
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color($color)
                    ->url(url('/teacher/students?assignment_id=' . $assignmentId));
            })
            ->values()
            ->all();
    }
}
