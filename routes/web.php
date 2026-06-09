<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    Route::resource('canteen-transactions', \App\Http\Controllers\CanteenTransactionController::class);
    Route::resource('attendances', \App\Http\Controllers\AttendanceController::class);
    Route::resource('employee-activities', \App\Http\Controllers\EmployeeActivityController::class);
});

Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'id'])) {
        abort(400);
    }
    session()->put('locale', $locale);
    return redirect()->back();
})->name('lang.switch');

require __DIR__.'/auth.php';
