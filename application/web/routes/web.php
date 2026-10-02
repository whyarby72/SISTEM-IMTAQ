<?php

use App\Http\Controllers\Academic\AcademicAiController;
use App\Http\Controllers\Academic\AcademicDashboardController;
use App\Http\Controllers\Academic\AttendanceExceptionController;
use App\Http\Controllers\Academic\StudentAttendanceController;
use App\Http\Controllers\Admin\AcademicClassController;
use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AiProviderConfigurationController;
use App\Http\Controllers\Admin\MonthlyAttendanceReportController;
use App\Http\Controllers\Admin\ScheduleRuleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'active.account'])->group(function (): void {
    Route::get('/account/password/change', [AuthenticatedSessionController::class, 'editPassword'])->name('password.change');
    Route::put('/account/password/change', [AuthenticatedSessionController::class, 'updatePassword'])->name('password.update');
});

Route::middleware(['auth', 'active.account', 'feature:academic.attendance'])->prefix('academic/attendance')->name('academic.attendance.')->group(function (): void {
    Route::get('/exceptions', [AttendanceExceptionController::class, 'index'])->name('exceptions');
    Route::post('/exceptions/bulk-cancel', [AttendanceExceptionController::class, 'bulkCancel'])->name('exceptions.bulk-cancel');
    Route::post('/exceptions/bulk-snapshot', [AttendanceExceptionController::class, 'bulkSnapshot'])->name('exceptions.bulk-snapshot');
    Route::get('/reviews', [StudentAttendanceController::class, 'reviews'])->middleware('feature:academic.attendance_review')->name('reviews');
    Route::get('/reviews.csv', [StudentAttendanceController::class, 'exportReviewsCsv'])->name('reviews.csv');
    Route::get('/reviews.pdf', [StudentAttendanceController::class, 'exportReviewsPdf'])->name('reviews.pdf');
    Route::post('/corrections/{correctionRequest}/review', [StudentAttendanceController::class, 'reviewCorrection'])->name('corrections.review');
    Route::post('/corrections/{correctionRequest}/apply', [StudentAttendanceController::class, 'applyCorrection'])->name('corrections.apply');
    Route::post('/{session}/corrections/direct', [StudentAttendanceController::class, 'directCorrection'])->name('corrections.direct');
    Route::get('/{session}', [StudentAttendanceController::class, 'show'])->name('show');
    Route::post('/{session}/occurrence', [StudentAttendanceController::class, 'recordOccurrence'])->name('occurrence.record');
    Route::post('/{session}/occurrence/correction', [StudentAttendanceController::class, 'correctOccurrence'])->name('occurrence.correction');
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

Route::middleware(['auth', 'active.account'])->prefix('academic')->name('academic.')->group(function (): void {
    Route::post('/ai-assistant/query', [AcademicAiController::class, 'query'])
        ->middleware('throttle:academic-ai')
        ->middleware('feature:ai.academic_assistant')->name('ai-assistant.query');
    Route::get('/dashboard', [AcademicDashboardController::class, 'index'])->middleware('feature:academic.dashboard')->name('dashboard');
    Route::get('/dashboard/export', [AcademicDashboardController::class, 'export'])->middleware('feature:academic.dashboard')->name('dashboard.export');
    Route::get('/monthly-reports/july-2026', [MonthlyAttendanceReportController::class, 'index'])->middleware('feature:academic.reports')->name('monthly-reports.index');
    Route::get('/monthly-reports/july-2026/{class}.csv', [MonthlyAttendanceReportController::class, 'detailCsv'])->middleware('feature:academic.reports')->whereUuid('class')->name('monthly-reports.detail.csv');
    Route::get('/monthly-reports/july-2026/{class}.pdf', [MonthlyAttendanceReportController::class, 'detailPdf'])->middleware('feature:academic.reports')->whereUuid('class')->name('monthly-reports.detail.pdf');
    Route::get('/monthly-reports/july-2026/{class}', [MonthlyAttendanceReportController::class, 'detail'])->middleware('feature:academic.reports')->whereUuid('class')->name('monthly-reports.detail');
});

Route::middleware(['auth', 'active.account'])->prefix('admin/academic')->name('admin.academic.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->middleware('feature:academic.dashboard')->name('dashboard');
    Route::get('/monthly-reports/july-2026', [MonthlyAttendanceReportController::class, 'index'])->middleware('feature:academic.reports')->name('monthly-reports.index');
    Route::get('/monthly-reports/july-2026/{class}.csv', [MonthlyAttendanceReportController::class, 'detailCsv'])->middleware('feature:academic.reports')->whereUuid('class')->name('monthly-reports.detail.csv');
    Route::get('/monthly-reports/july-2026/{class}.pdf', [MonthlyAttendanceReportController::class, 'detailPdf'])->middleware('feature:academic.reports')->whereUuid('class')->name('monthly-reports.detail.pdf');
    Route::get('/monthly-reports/july-2026/{class}', [MonthlyAttendanceReportController::class, 'detail'])->middleware('feature:academic.reports')->whereUuid('class')->name('monthly-reports.detail');
    Route::post('/monthly-reports/july-2026/publish', [MonthlyAttendanceReportController::class, 'publish'])->middleware('feature:academic.reports')->name('monthly-reports.publish');
    Route::get('/monthly-reports/july-2026.csv', [MonthlyAttendanceReportController::class, 'exportCsv'])->middleware('feature:academic.reports')->name('monthly-reports.csv');
    Route::get('/monthly-reports/july-2026.pdf', [MonthlyAttendanceReportController::class, 'exportPdf'])->middleware('feature:academic.reports')->name('monthly-reports.pdf');
    Route::get('/students', [StudentController::class, 'index'])->middleware('feature:academic.students')->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->middleware('feature:academic.students')->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->middleware('feature:academic.students')->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->middleware('feature:academic.students')->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('feature:academic.students')->name('students.update');
    Route::get('/classes', [AcademicClassController::class, 'index'])->middleware('feature:academic.classes')->name('classes.index');
    Route::get('/structure', [AcademicStructureController::class, 'index'])->middleware('feature:academic.structure')->name('structure.index');
    Route::post('/structure/grade-levels', [AcademicStructureController::class, 'storeGradeLevel'])->middleware('feature:academic.structure')->name('structure.grade-levels.store');
    Route::post('/structure/homerooms', [AcademicStructureController::class, 'storeHomeroom'])->middleware('feature:academic.structure')->name('structure.homerooms.store');
    Route::get('/structure/homerooms/{assignment}/edit', [AcademicStructureController::class, 'editHomeroom'])->middleware('feature:academic.structure')->name('structure.homerooms.edit');
    Route::put('/structure/homerooms/{assignment}', [AcademicStructureController::class, 'updateHomeroom'])->middleware('feature:academic.structure')->name('structure.homerooms.update');
    Route::get('/classes/create', [AcademicClassController::class, 'create'])->middleware('feature:academic.classes')->name('classes.create');
    Route::post('/classes', [AcademicClassController::class, 'store'])->middleware('feature:academic.classes')->name('classes.store');
    Route::get('/classes/{class}/edit', [AcademicClassController::class, 'edit'])->middleware('feature:academic.classes')->name('classes.edit');
    Route::put('/classes/{class}', [AcademicClassController::class, 'update'])->middleware('feature:academic.classes')->name('classes.update');
    Route::get('/staff', [StaffController::class, 'index'])->middleware('feature:academic.staff')->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->middleware('feature:academic.staff')->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->middleware('feature:academic.staff')->name('staff.store');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->middleware('feature:academic.staff')->name('staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->middleware('feature:academic.staff')->name('staff.update');
    Route::get('/subjects', [SubjectController::class, 'index'])->middleware('feature:academic.subjects')->name('subjects.index');
    Route::get('/subjects/create', [SubjectController::class, 'create'])->middleware('feature:academic.subjects')->name('subjects.create');
    Route::post('/subjects', [SubjectController::class, 'store'])->middleware('feature:academic.subjects')->name('subjects.store');
    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->middleware('feature:academic.subjects')->name('subjects.edit');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->middleware('feature:academic.subjects')->name('subjects.update');
    Route::get('/schedules', [ScheduleRuleController::class, 'index'])->middleware('feature:academic.schedules')->name('schedules.index');
    Route::post('/schedules/official/validate', [ScheduleRuleController::class, 'validateOfficial'])->middleware('feature:academic.schedules')->name('schedules.official.validate');
    Route::post('/schedules/official/publish', [ScheduleRuleController::class, 'publishOfficial'])->middleware('feature:academic.schedules')->name('schedules.official.publish');
    Route::get('/schedules/create', [ScheduleRuleController::class, 'create'])->middleware('feature:academic.schedules')->name('schedules.create');
    Route::get('/teaching-assignments/create', [ScheduleRuleController::class, 'createTeachingAssignment'])->middleware('feature:academic.schedules')->name('teaching-assignments.create');
    Route::post('/teaching-assignments', [ScheduleRuleController::class, 'storeTeachingAssignment'])->middleware('feature:academic.schedules')->name('teaching-assignments.store');
    Route::post('/schedules', [ScheduleRuleController::class, 'store'])->middleware('feature:academic.schedules')->name('schedules.store');
    Route::get('/schedules/{schedule}/edit', [ScheduleRuleController::class, 'edit'])->middleware('feature:academic.schedules')->name('schedules.edit');
    Route::put('/schedules/{schedule}', [ScheduleRuleController::class, 'update'])->middleware('feature:academic.schedules')->name('schedules.update');
    Route::delete('/schedules/{schedule}', [ScheduleRuleController::class, 'destroy'])->middleware('feature:academic.schedules')->name('schedules.destroy');
    Route::post('/schedules/{schedule}/archive', [ScheduleRuleController::class, 'archive'])->middleware('feature:academic.schedules')->name('schedules.archive');
});

Route::middleware(['auth', 'active.account', 'feature:platform.system_settings', 'throttle:admin-ai-provider'])->prefix('admin/system/ai-provider')->name('admin.system.ai-provider.')->group(function (): void {
    Route::get('/', [AiProviderConfigurationController::class, 'index'])->name('index');
    Route::post('/credentials', [AiProviderConfigurationController::class, 'storeCredential'])->name('credentials.store');
    Route::post('/credentials/{credential}/verify', [AiProviderConfigurationController::class, 'verifyCredential'])->name('credentials.verify');
    Route::post('/credentials/{credential}/revoke', [AiProviderConfigurationController::class, 'revoke'])->name('credentials.revoke');
    Route::post('/credentials/{credential}/models', [AiProviderConfigurationController::class, 'discoverModels'])->name('credentials.models');
    Route::post('/configurations', [AiProviderConfigurationController::class, 'storeConfiguration'])->name('configurations.store');
    Route::post('/configurations/{configuration}/verify', [AiProviderConfigurationController::class, 'verifyConfiguration'])->name('configurations.verify');
    Route::post('/configurations/{configuration}/activate', [AiProviderConfigurationController::class, 'activate'])->name('configurations.activate');
    Route::post('/runtime', [AiProviderConfigurationController::class, 'toggleRuntime'])->name('runtime.toggle');
});

Route::middleware(['auth', 'active.account', 'feature:platform.user_access'])->prefix('admin/system/users')->name('admin.system.users.')->group(function (): void {
    Route::get('/', [UserAccessController::class, 'index'])->name('index');
    Route::get('/create', [UserAccessController::class, 'create'])->name('create');
    Route::post('/', [UserAccessController::class, 'store'])->name('store');
    Route::get('/{user}', [UserAccessController::class, 'show'])->name('show');
    Route::put('/{user}/account', [UserAccessController::class, 'updateAccount'])->name('account.update');
    Route::post('/{user}/roles', [UserAccessController::class, 'updateRole'])->name('roles.update');
    Route::delete('/{user}/roles/{assignment}', [UserAccessController::class, 'revokeRole'])->name('roles.revoke');
    Route::put('/{user}/features', [UserAccessController::class, 'updateFeatures'])->name('features.update');
    Route::put('/{user}/preferences', [UserAccessController::class, 'updatePreferences'])->name('preferences.update');
});
