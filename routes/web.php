<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\TeacherPortalController;
// use App\Http\Controllers\UserController;  // TODO: re-habilitar con el sistema de roles
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

// ─── ALL authenticated routes ────────────────────────────────────────────────
// NOTA: Los middlewares de rol están comentados temporalmente para que todos
// los usuarios autenticados puedan acceder a todas las funciones.
// TODO: Re-habilitar los grupos con ->middleware('role:xxx') cuando se
//       configure correctamente el sistema de roles.
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Settings
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    // ── Portals ──────────────────────────────────────────────────────────────
    // Route::middleware('role:alumno')->group(function () {   // TODO: re-habilitar roles
        Route::get('/portal/student', [StudentPortalController::class, 'index'])->name('portal.student');
    // });

    // Route::middleware('role:admin,profesor')->group(function () {   // TODO: re-habilitar roles
        Route::get('/portal/teacher', [TeacherPortalController::class, 'index'])->name('portal.teacher');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/take', [AttendanceController::class, 'take'])->name('attendance.take');
        Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    // });

    // ── Students ─────────────────────────────────────────────────────────────
    // Route::middleware('role:admin,enfermeria')->group(function () {   // TODO: re-habilitar roles
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    // });

    // Static student routes MUST come before the {student} wildcard
    // Route::middleware('role:admin')->group(function () {   // TODO: re-habilitar roles
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    // });

    // Route::middleware('role:admin,enfermeria,profesor')->group(function () {   // TODO: re-habilitar roles
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    // });

    // ── Medical checkups ─────────────────────────────────────────────────────
    // Route::middleware('role:admin,enfermeria')->group(function () {   // TODO: re-habilitar roles
        Route::get('/medical-checkups', [MedicalCheckupController::class, 'index'])->name('medical_checkups.index');
        Route::get('/medical-checkups/create', [MedicalCheckupController::class, 'create'])->name('medical_checkups.create');
        Route::post('/medical-checkups', [MedicalCheckupController::class, 'store'])->name('medical_checkups.store');
        Route::get('/medical-checkups/report', [MedicalCheckupController::class, 'report'])->name('medical_checkups.report');
        Route::get('/medical-checkups/report/pdf', [MedicalCheckupController::class, 'reportPdf'])->name('medical_checkups.report_pdf');
    // });

    // ── Previously admin-only routes (now open to all authenticated users) ───
    // Route::middleware('role:admin')->group(function () {   // TODO: re-habilitar roles

        // ── Gestión de usuarios - COMENTADO TEMPORALMENTE ─────────────────
        // TODO: Re-habilitar cuando se configure correctamente el sistema de roles
        /*
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        */

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
        Route::post('/teachers/{teacher}/assign-class', [TeacherController::class, 'assignClass'])->name('teachers.assignClass');
        Route::post('/teachers/{teacher}/unassign-class', [TeacherController::class, 'unassignClass'])->name('teachers.unassignClass');

        // Students CRUD
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::get('students/{student}/enroll', [StudentController::class, 'enrollClassForm'])->name('students.enrollClassForm');
        Route::post('students/{student}/enroll', [StudentController::class, 'enrollClass'])->name('students.enroll');
        Route::post('students/{student}/unenroll', [StudentController::class, 'unenrollClass'])->name('students.unenroll');

        // Student pauses
        Route::get('students/{student}/pauses', [App\Http\Controllers\StudentPauseController::class, 'index'])->name('students.pauses.index');
        Route::post('students/{student}/pauses', [App\Http\Controllers\StudentPauseController::class, 'store'])->name('students.pauses.store');
        Route::get('students/{student}/pauses/{pause}/edit', [App\Http\Controllers\StudentPauseController::class, 'edit'])->name('students.pauses.edit');
        Route::put('students/{student}/pauses/{pause}', [App\Http\Controllers\StudentPauseController::class, 'update'])->name('students.pauses.update');
        Route::delete('students/{student}/pauses/{pause}', [App\Http\Controllers\StudentPauseController::class, 'destroy'])->name('students.pauses.destroy');

        // Subjects
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/events', [SubjectController::class, 'events']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
        Route::put('/subjects/{subject}/move', [SubjectController::class, 'move']);
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);

        // Subject prices
        Route::get('/subject-prices', [SubjectPriceController::class, 'index'])->name('subject-prices.index');
        Route::post('/subject-prices', [SubjectPriceController::class, 'update'])->name('subject-prices.update');

        // Payment methods
        Route::resource('payment_methods', PaymentMethodController::class)->except(['show']);

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');

        // Medical checkups: edit/delete
        Route::get('/medical-checkups/{medicalCheckup}/edit', [MedicalCheckupController::class, 'edit'])->name('medical_checkups.edit');
        Route::put('/medical-checkups/{medicalCheckup}', [MedicalCheckupController::class, 'update'])->name('medical_checkups.update');
        Route::delete('/medical-checkups/{medicalCheckup}', [MedicalCheckupController::class, 'destroy'])->name('medical_checkups.destroy');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/class-enrollees', [ReportController::class, 'classEnrolleesForm'])->name('class_enrollees.form');
            Route::get('/class-enrollees/view', [ReportController::class, 'classEnrolleesPage'])->name('class_enrollees.view');
            Route::get('/class-enrollees/print', [ReportController::class, 'classEnrollees'])->name('class_enrollees.print');
            Route::get('/class-enrollees/pdf', [ReportController::class, 'classEnrolleesPdf'])->name('class_enrollees.pdf');
            Route::get('/debtors', [ReportController::class, 'debtorsForm'])->name('debtors.form');
            Route::get('/debtors/view', [ReportController::class, 'debtorsPage'])->name('debtors.view');
            Route::get('/debtors/print', [ReportController::class, 'debtors'])->name('debtors.print');
            Route::get('/debtors/pdf', [ReportController::class, 'debtorsPdf'])->name('debtors.pdf');
            Route::get('/payments-by-student', [ReportController::class, 'paymentsByStudentForm'])->name('payments_by_student.form');
            Route::get('/payments-by-student/view', [ReportController::class, 'paymentsByStudentPage'])->name('payments_by_student.view');
            Route::get('/payments-by-student/print', [ReportController::class, 'paymentsByStudent'])->name('payments_by_student.print');
            Route::get('/payments-by-student/pdf', [ReportController::class, 'paymentsByStudentPdf'])->name('payments_by_student.pdf');
            Route::get('/all-students/view', [ReportController::class, 'allStudentsPage'])->name('all_students.view');
            Route::get('/all-students/print', [ReportController::class, 'allStudents'])->name('all_students.print');
            Route::get('/all-students/pdf', [ReportController::class, 'allStudentsPdf'])->name('all_students.pdf');
            Route::get('/accounting', [ReportController::class, 'accountingForm'])->name('accounting.form');
            Route::get('/accounting/view', [ReportController::class, 'accountingPage'])->name('accounting.view');
            Route::get('/accounting/excel', [ReportController::class, 'accountingExcel'])->name('accounting.excel');
        });

        // Backup
        Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');

    // }); // end role:admin group

}); // end auth middleware

require __DIR__.'/auth.php';