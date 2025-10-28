<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::get('/subjects/events', [SubjectController::class, 'events']);
Route::post('/subjects', [SubjectController::class, 'store']);
Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
Route::put('/subjects/{subject}/move', [SubjectController::class, 'move']);
Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);


// Extra: Anotar a clase y registrar pago
Route::get('students/{student}/enroll', [StudentController::class, 'enrollClassForm'])
    ->name('students.enrollClassForm');

Route::post('students/{student}/enroll', [StudentController::class, 'enrollClass'])
    ->name('students.enroll');

Route::post('students/{student}/unenroll', [StudentController::class, 'unenrollClass'])
    ->name('students.unenroll');
    
    //Route::get('/students/{student}/register-payment', [StudentController::class, 'registerPaymentForm'])->name('students.registerPaymentForm');
//Route::post('/students/{student}/register-payment', [StudentController::class, 'registerPayment'])->name('students.registerPayment');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

require __DIR__.'/auth.php';
