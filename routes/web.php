<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectPriceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    
// Rutas para tipos de pago (ABM)
Route::resource('payment_methods', PaymentMethodController::class)->except(['show'])->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

Route::middleware(['web'])->group(function () {
    // Página para ver/editar precios por defecto (con profesor / sin profesor)
    Route::get('/subject-prices', [SubjectPriceController::class, 'index'])->name('subject-prices.index');
    Route::post('/subject-prices', [SubjectPriceController::class, 'update'])->name('subject-prices.update');
});

Route::middleware(['web'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
});
Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Inscritos por clase
    Route::get('/reports/class-enrollees', [ReportController::class, 'classEnrolleesForm'])->name('reports.class_enrollees.form');
    Route::get('/reports/class-enrollees/print', [ReportController::class, 'classEnrollees'])->name('reports.class_enrollees.print'); // imprime / pdf (param via query)
    Route::get('/reports/class-enrollees/pdf', [ReportController::class, 'classEnrolleesPdf'])->name('reports.class_enrollees.pdf');

    // Alumnos deudores
    Route::get('/reports/debtors', [ReportController::class, 'debtorsForm'])->name('reports.debtors.form');
    Route::get('/reports/debtors/print', [ReportController::class, 'debtors'])->name('reports.debtors.print');
    Route::get('/reports/debtors/pdf', [ReportController::class, 'debtorsPdf'])->name('reports.debtors.pdf');

    // Pagos por alumno
    Route::get('/reports/payments-by-student', [ReportController::class, 'paymentsByStudentForm'])->name('reports.payments_by_student.form');
    Route::get('/reports/payments-by-student/print', [ReportController::class, 'paymentsByStudent'])->name('reports.payments_by_student.print');
    Route::get('/reports/payments-by-student/pdf', [ReportController::class, 'paymentsByStudentPdf'])->name('reports.payments_by_student.pdf');

    // Todos los alumnos
    Route::get('/reports/all-students/print', [ReportController::class, 'allStudents'])->name('reports.all_students.print');
    Route::get('/reports/all-students/pdf', [ReportController::class, 'allStudentsPdf'])->name('reports.all_students.pdf');
});

Route::middleware(['web', 'auth'])->group(function () {
    // ... otras rutas de reportes ...
    Route::get('/reports/class-enrollees/view', [ReportController::class, 'classEnrolleesPage'])->name('reports.class_enrollees.view');
});

Route::middleware(['web', 'auth'])->group(function () {
    // ... otras rutas ...
    Route::get('/reports/debtors/view', [ReportController::class, 'debtorsPage'])->name('reports.debtors.view');
});

Route::middleware(['web', 'auth'])->group(function () {
    // ... otras rutas ...
    Route::get('/reports/payments-by-student/view', [ReportController::class, 'paymentsByStudentPage'])->name('reports.payments_by_student.view');
});

// name=routes/web.php
Route::middleware(['web', 'auth'])->group(function () {
    // ... otras rutas de reportes ...
    Route::get('/reports/all-students/view', [\App\Http\Controllers\ReportController::class, 'allStudentsPage'])->name('reports.all_students.view');
});
require __DIR__.'/auth.php';
