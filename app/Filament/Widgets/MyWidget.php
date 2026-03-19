<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalStudents = Student::count();
        $totalPayments = (float) Payment::query()->where('is_verified', true)->sum('amount');
        $totalDebt = (float) Student::all()->sum('total_debt');
        $unverifiedPayments = Payment::query()->where('is_verified', false)->count();

        return [
            Stat::make('Total Students', number_format($totalStudents))
                ->description('All registered students')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Total Payments', 'TSH ' . number_format($totalPayments, 2))
                ->description('Verified payments received')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Debt', 'TSH ' . number_format($totalDebt, 2))
                ->description('Outstanding balances')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Unverified Payments', number_format($unverifiedPayments))
                ->description($unverifiedPayments === 0 ? 'All verified' : 'Pending verification')
                ->descriptionIcon($unverifiedPayments === 0 ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                ->color($unverifiedPayments === 0 ? 'success' : 'warning'),
        ];
    }
}
