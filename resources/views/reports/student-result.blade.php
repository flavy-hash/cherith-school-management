<!DOCTYPE html>
<html>
<head>
    <title>Student Result Report - {{ $schoolName }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .school-name { font-size: 24px; font-weight: bold; }
        .report-title { font-size: 18px; margin: 10px 0; }
        .report-date { font-size: 14px; color: #666; }
        .student-info { margin-bottom: 30px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info th, .student-info td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .student-info th { background-color: #f2f2f2; }
        .results-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .results-table th, .results-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .results-table th { background-color: #f2f2f2; }
        .score-cell { text-align: center; font-weight: bold; }
        .score-excellent { color: #10b981; }
        .score-good { color: #f59e0b; }
        .score-poor { color: #ef4444; }
        .summary { margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; }
        .summary strong { color: #1f2937; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        <div class="report-title">Student Result Report</div>
        <div class="report-date">Generated on: {{ $reportDate }}</div>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <th>Admission Number</th>
                <td>{{ $student->admission_number }}</td>
                <th>Class</th>
                <td>{{ $student->standard->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Student Name</th>
                <td>{{ $student->full_name }}</td>
                <th>Term/Year</th>
                <td>{{ ucfirst(str_replace('_', ' ', $term)) }} - {{ $year }}</td>
            </tr>
        </table>
    </div>

    <h3>Subject Results</h3>
    <table class="results-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $result)
                <tr>
                    <td>{{ $result->subject->name }}</td>
                    <td class="score-cell {{ $result->score >= 75 ? 'score-excellent' : ($result->score >= 50 ? 'score-good' : 'score-poor') }}">
                        {{ $result->score }}
                    </td>
                    <td>
                        @if($result->score >= 75)
                            <span style="color: #10b981;">Excellent</span>
                        @elseif($result->score >= 50)
                            <span style="color: #f59e0b;">Good</span>
                        @else
                            <span style="color: #ef4444;">Poor</span>
                        @endif
                    </td>
                    <td>
                        @if($result->score >= 75)
                            Outstanding performance
                        @elseif($result->score >= 50)
                            Satisfactory performance
                        @else
                            Needs improvement
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No results found for this term/year</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($results->isNotEmpty())
        <div class="summary">
            <h3>Performance Summary</h3>
            <p><strong>Total Subjects:</strong> {{ $totalSubjects }}</p>
            <p><strong>Average Score:</strong> {{ $averageScore }}%</p>
            <p><strong>Pass Rate:</strong> {{ $passRate }}% ({{ $passedSubjects }}/{{ $totalSubjects }} subjects passed)</p>
            <p><strong>Overall Performance:</strong> 
                @if($passRate >= 75)
                    <span style="color: #10b981;">Excellent</span>
                @elseif($passRate >= 50)
                    <span style="color: #f59e0b;">Good</span>
                @else
                    <span style="color: #ef4444;">Needs Improvement</span>
                @endif
            </p>
        </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
        <p>{{ $schoolName }} - Student Management System</p>
    </div>
</body>
</html>
