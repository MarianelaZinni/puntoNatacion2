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
use App\Http\Controllers\BackupController;
use App\Http\Controllers\MedicalCheckupController;
use App\Http\Controllers\TeacherController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Teachers (Profesores)
Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
Route::post('/teachers/{teacher}/assign-class', [TeacherController::class, 'assignClass'])->name('teachers.assignClass');
Route::post('/teachers/{teacher}/unassign-class', [TeacherController::class, 'unassignClass'])->name('teachers.unassignClass');

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
    Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');  // ← NUEVO
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');  // ← NUEVO
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');  // ← NUEVO
});
Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');

// Medical Checkups (Revisiones médicas)
Route::middleware(['web'])->group(function () {
    Route::get('/medical-checkups', [MedicalCheckupController::class, 'index'])->name('medical_checkups.index');
    Route::get('/medical-checkups/create', [MedicalCheckupController::class, 'create'])->name('medical_checkups.create');
    Route::post('/medical-checkups', [MedicalCheckupController::class, 'store'])->name('medical_checkups.store');
    Route::get('/medical-checkups/report', [MedicalCheckupController::class, 'report'])->name('medical_checkups.report');
    Route::get('/medical-checkups/report/pdf', [MedicalCheckupController::class, 'reportPdf'])->name('medical_checkups.report_pdf');
    Route::get('/medical-checkups/{medicalCheckup}/edit', [MedicalCheckupController::class, 'edit'])->name('medical_checkups.edit');
    Route::put('/medical-checkups/{medicalCheckup}', [MedicalCheckupController::class, 'update'])->name('medical_checkups.update');
    Route::delete('/medical-checkups/{medicalCheckup}', [MedicalCheckupController::class, 'destroy'])->name('medical_checkups.destroy');
});

// Ruta para backup de base de datos  
Route::middleware(['auth'])->group(function () {
    Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');
});


Route::middleware(['web', 'auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');

    // Inscritos por clase
    Route::get('/class-enrollees', [ReportController::class, 'classEnrolleesForm'])->name('class_enrollees.form');
    Route::get('/class-enrollees/view', [ReportController::class, 'classEnrolleesPage'])->name('class_enrollees.view');
    Route::get('/class-enrollees/print', [ReportController::class, 'classEnrollees'])->name('class_enrollees.print');
    Route::get('/class-enrollees/pdf', [ReportController::class, 'classEnrolleesPdf'])->name('class_enrollees.pdf');

    // Alumnos deudores
    Route::get('/debtors', [ReportController::class, 'debtorsForm'])->name('debtors.form');
    Route::get('/debtors/view', [ReportController::class, 'debtorsPage'])->name('debtors.view');
    Route::get('/debtors/print', [ReportController::class, 'debtors'])->name('debtors.print');
    Route::get('/debtors/pdf', [ReportController::class, 'debtorsPdf'])->name('debtors.pdf');

    // Pagos por alumno
    Route::get('/payments-by-student', [ReportController::class, 'paymentsByStudentForm'])->name('payments_by_student.form');
    Route::get('/payments-by-student/view', [ReportController::class, 'paymentsByStudentPage'])->name('payments_by_student.view');
    Route::get('/payments-by-student/print', [ReportController::class, 'paymentsByStudent'])->name('payments_by_student.print');
    Route::get('/payments-by-student/pdf', [ReportController::class, 'paymentsByStudentPdf'])->name('payments_by_student.pdf');

    // Todos los alumnos
    Route::get('/all-students/view', [ReportController::class, 'allStudentsPage'])->name('all_students.view');
    Route::get('/all-students/print', [ReportController::class, 'allStudents'])->name('all_students.print');
    Route::get('/all-students/pdf', [ReportController::class, 'allStudentsPdf'])->name('all_students.pdf');
});
require __DIR__.'/auth.php';