<?php

use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\TeacherResultsController;
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

Route::middleware(['auth', 'teacher.subject'])->prefix('teacher')->group(function () {
    Route::get('/', fn () => redirect()->route('teacher.results.index'));
    Route::get('/results', [TeacherResultsController::class, 'index'])->name('teacher.results.index');
    Route::post('/results', [TeacherResultsController::class, 'store'])->name('teacher.results.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/payments/{payment}/receipt', [PaymentReceiptController::class, 'show'])
        ->name('filament.admin.payments.receipt');

    Route::get('/admin/payments/receipts/bulk', [PaymentReceiptController::class, 'bulk'])
        ->name('filament.admin.payments.receipts.bulk');
});
