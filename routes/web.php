<?php

use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\StudentResultReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::get('/register', function () {
    return redirect('/admin/register');
})->name('register');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/payments/{payment}/receipt', [PaymentReceiptController::class, 'show'])
        ->name('filament.admin.payments.receipt');

    Route::get('/admin/payments/receipts/bulk', [PaymentReceiptController::class, 'bulk'])
        ->name('filament.admin.payments.receipts.bulk');

    Route::get('/admin/students/{student}/result-report', [StudentResultReportController::class, 'show'])
        ->name('filament.admin.students.result-report');
});
