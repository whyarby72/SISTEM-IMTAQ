<?php

use App\Http\Controllers\Academic\AcademicDashboardController;
use App\Http\Controllers\Academic\AttendanceExceptionController;
use App\Http\Controllers\Academic\StudentAttendanceController;
use App\Http\Controllers\Admin\AcademicClassController;
use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\MonthlyAttendanceReportController;
use App\Http\Controllers\Admin\ScheduleRuleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
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
    Route::post('/exceptions/bulk-cancel', [AttendanceExceptionController::class, 'bulkCancel'])->name('exceptions.bulk-cancel');
    Route::post('/exceptions/bulk-snapshot', [AttendanceExceptionController::class, 'bulkSnapshot'])->name('exceptions.bulk-snapshot');
    Route::get('/reviews', [StudentAttendanceController::class, 'reviews'])->name('reviews');
    Route::get('/reviews.csv', [StudentAttendanceController::class, 'exportReviewsCsv'])->name('reviews.csv');
    Route::get('/reviews.pdf', [StudentAttendanceController::class, 'exportReviewsPdf'])->name('reviews.pdf');
    Route::post('/corrections/{correctionRequest}/review', [StudentAttendanceController::class, 'reviewCorrection'])->name('corrections.review');
    Route::post('/corrections/{correctionRequest}/apply', [StudentAttendanceController::class, 'applyCorrection'])->name('corrections.apply');
    Route::post('/{session}/corrections/direct', [StudentAttendanceController::class, 'directCorrection'])->name('corrections.direct');
    Route::get('/{session}', [StudentAttendanceController::class, 'show'])->name('show');
    Route::post('/{session}/corrections', [StudentAttendanceController::class, 'requestCorrection'])->name('corrections.request');
    Route::post('/{session}/substitution', [StudentAttendanceController::class, 'substitute'])->name('substitution');
    Route::post('/{session}/teacher-attendance', [StudentAttendanceController::class, 'recordTeacherAttendance'])->name('teacher-attendance');
    Route::post('/{session}/snapshot-participants', [StudentAttendanceController::class, 'snapshotParticipants'])->name('snapshot-participants');
    Route::post('/{session}/cancel', [StudentAttendanceController::class, 'cancel'])->name('cancel');
    Route::get('/{session}/review', [StudentAttendanceController::class, 'review'])->name('review');
    Route::get('/{session}/review.csv', [StudentAttendanceController::class, 'exportReviewDetailCsv'])->name('review.csv');
    Route::get('/{session}/review.pdf', [StudentAttendanceController::class, 'exportReviewDetailPdf'])->name('review.pdf');
    Route::post('/{session}/draft', [StudentAttendanceController::class, 'saveDraft'])->name('draft');
    Route::post('/{session}/finalize', [StudentAttendanceController::class, 'finalize'])->name('finalize');
});

Route::middleware('auth')->prefix('academic')->name('academic.')->group(function (): void {
    Route::get('/dashboard', [AcademicDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [AcademicDashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/monthly-reports/july-2026', [MonthlyAttendanceReportController::class, 'index'])->name('monthly-reports.index');
    Route::get('/monthly-reports/july-2026/{class}.csv', [MonthlyAttendanceReportController::class, 'detailCsv'])->whereUuid('class')->name('monthly-reports.detail.csv');
    Route::get('/monthly-reports/july-2026/{class}.pdf', [MonthlyAttendanceReportController::class, 'detailPdf'])->whereUuid('class')->name('monthly-reports.detail.pdf');
    Route::get('/monthly-reports/july-2026/{class}', [MonthlyAttendanceReportController::class, 'detail'])->whereUuid('class')->name('monthly-reports.detail');
});

Route::middleware('auth')->prefix('admin/academic')->name('admin.academic.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/monthly-reports/july-2026', [MonthlyAttendanceReportController::class, 'index'])->name('monthly-reports.index');
    Route::get('/monthly-reports/july-2026/{class}.csv', [MonthlyAttendanceReportController::class, 'detailCsv'])->whereUuid('class')->name('monthly-reports.detail.csv');
    Route::get('/monthly-reports/july-2026/{class}.pdf', [MonthlyAttendanceReportController::class, 'detailPdf'])->whereUuid('class')->name('monthly-reports.detail.pdf');
    Route::get('/monthly-reports/july-2026/{class}', [MonthlyAttendanceReportController::class, 'detail'])->whereUuid('class')->name('monthly-reports.detail');
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
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::get('/schedules', [ScheduleRuleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules/official/validate', [ScheduleRuleController::class, 'validateOfficial'])->name('schedules.official.validate');
    Route::post('/schedules/official/publish', [ScheduleRuleController::class, 'publishOfficial'])->name('schedules.official.publish');
    Route::get('/schedules/create', [ScheduleRuleController::class, 'create'])->name('schedules.create');
    Route::get('/teaching-assignments/create', [ScheduleRuleController::class, 'createTeachingAssignment'])->name('teaching-assignments.create');
    Route::post('/teaching-assignments', [ScheduleRuleController::class, 'storeTeachingAssignment'])->name('teaching-assignments.store');
    Route::post('/schedules', [ScheduleRuleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}/edit', [ScheduleRuleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{schedule}', [ScheduleRuleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [ScheduleRuleController::class, 'destroy'])->name('schedules.destroy');
    Route::post('/schedules/{schedule}/archive', [ScheduleRuleController::class, 'archive'])->name('schedules.archive');
});
