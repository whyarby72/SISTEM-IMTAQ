<?php

use App\Http\Controllers\Academic\AcademicDashboardController;
use App\Http\Controllers\Academic\AttendanceExceptionController;
use App\Http\Controllers\Academic\StudentAttendanceController;
use App\Http\Controllers\Admin\AcademicClassController;
use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ScheduleRuleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\MonthlyAttendanceReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('academic/attendance')->name('academic.attendance.')->group(function (): void {
    Route::get('/exceptions', [AttendanceExceptionController::class, 'index'])->name('exceptions');
    Route::get('/{session}', [StudentAttendanceController::class, 'show'])->name('show');
    Route::post('/{session}/draft', [StudentAttendanceController::class, 'saveDraft'])->name('draft');
    Route::post('/{session}/finalize', [StudentAttendanceController::class, 'finalize'])->name('finalize');
});

Route::middleware('auth')->prefix('academic')->name('academic.')->group(function (): void {
    Route::get('/dashboard', [AcademicDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [AcademicDashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/monthly-reports/july-2026', [MonthlyAttendanceReportController::class, 'index'])->name('monthly-reports.index');
    Route::get('/monthly-reports/july-2026/{class}', [MonthlyAttendanceReportController::class, 'detail'])->name('monthly-reports.detail');
});

Route::middleware('auth')->prefix('admin/academic')->name('admin.academic.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/monthly-reports/july-2026', [MonthlyAttendanceReportController::class, 'index'])->name('monthly-reports.index');
    Route::get('/monthly-reports/july-2026/{class}', [MonthlyAttendanceReportController::class, 'detail'])->name('monthly-reports.detail');
    Route::post('/monthly-reports/july-2026/publish', [MonthlyAttendanceReportController::class, 'publish'])->name('monthly-reports.publish');
    Route::get('/monthly-reports/july-2026.csv', [MonthlyAttendanceReportController::class, 'exportCsv'])->name('monthly-reports.csv');
    Route::get('/monthly-reports/july-2026.pdf', [MonthlyAttendanceReportController::class, 'exportPdf'])->name('monthly-reports.pdf');
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::get('/classes', [AcademicClassController::class, 'index'])->name('classes.index');
    Route::get('/structure', [AcademicStructureController::class, 'index'])->name('structure.index');
    Route::post('/structure/grade-levels', [AcademicStructureController::class, 'storeGradeLevel'])->name('structure.grade-levels.store');
    Route::post('/structure/homerooms', [AcademicStructureController::class, 'storeHomeroom'])->name('structure.homerooms.store');
    Route::get('/structure/homerooms/{assignment}/edit', [AcademicStructureController::class, 'editHomeroom'])->name('structure.homerooms.edit');
    Route::put('/structure/homerooms/{assignment}', [AcademicStructureController::class, 'updateHomeroom'])->name('structure.homerooms.update');
    Route::get('/classes/create', [AcademicClassController::class, 'create'])->name('classes.create');
    Route::post('/classes', [AcademicClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}/edit', [AcademicClassController::class, 'edit'])->name('classes.edit');
    Route::put('/classes/{class}', [AcademicClassController::class, 'update'])->name('classes.update');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::get('/schedules', [ScheduleRuleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [ScheduleRuleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [ScheduleRuleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}/edit', [ScheduleRuleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{schedule}', [ScheduleRuleController::class, 'update'])->name('schedules.update');
});
