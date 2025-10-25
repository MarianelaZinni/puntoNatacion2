<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/alumnos', [StudentController::class, 'index'])->name('students.index');
Route::get('/alumnos/create', [StudentController::class, 'create'])->name('students.create');
Route::post('/alumnos', [StudentController::class, 'store'])->name('students.store');
Route::get('/alumnos/{alumno}', [StudentController::class, 'show'])->name('students.show');
Route::get('/alumnos/{alumno}/edit', [StudentController::class, 'edit'])->name('students.edit');
Route::put('/alumnos/{alumno}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/alumnos/{alumno}', [StudentController::class, 'destroy'])->name('students.destroy');

// Extra: Anotar a clase y registrar pago
Route::get('/alumnos/{alumno}/enroll-class', [StudentController::class, 'enrollClassForm'])->name('students.enrollClassForm');
Route::post('/alumnos/{alumno}/enroll-class', [StudentController::class, 'enrollClass'])->name('students.enrollClass');
//Route::get('/alumnos/{alumno}/register-payment', [StudentController::class, 'registerPaymentForm'])->name('students.registerPaymentForm');
//Route::post('/alumnos/{alumno}/register-payment', [StudentController::class, 'registerPayment'])->name('students.registerPayment');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

require __DIR__.'/auth.php';
