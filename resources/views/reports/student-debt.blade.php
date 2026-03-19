<!DOCTYPE html>
<html>
<head>
    <title>Student Debt Report - Cherith Junior School</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .school-name { font-size: 24px; font-weight: bold; }
        .report-title { font-size: 18px; margin: 10px 0; }
        .report-date { font-size: 14px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; background-color: #f8f9fa; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        <div class="report-title">Student Debt Report</div>
        <div class="report-date">Generated on: {{ date('F d, Y', strtotime($reportDate)) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Admission No.</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Term 1 Balance</th>
                <th>Term 2 Balance</th>
                <th>Total Debt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $student->admission_number }}</td>
                <td>{{ $student->full_name }}</td>
                <td>{{ $student->standard->name }}</td>
                <td>TSH {{ number_format(max(0, $student->term_one_balance), 2) }}</td>
                <td>TSH {{ number_format(max(0, $student->term_two_balance), 2) }}</td>
                <td>TSH {{ number_format($student->total_debt, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">Total Outstanding Debt:</td>
                <td>TSH {{ number_format($totalDebt, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by Cherith Junior School Management System.</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
