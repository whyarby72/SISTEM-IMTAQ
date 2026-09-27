<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ReportCard;
use App\Domains\Academic\Models\ReportCardAttendanceLine;
use App\Domains\Academic\Models\ReportCardSubjectLine;
use App\Domains\Academic\Models\ReportCardVersion;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentAttendance;
use App\Models\User;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReportCardDraftService
{
    public function __construct(
        private readonly ReportCardReadinessService $readiness,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(Student $student, Semester $semester, AcademicClass $class, User $actor): ReportCardVersion
    {
        $readiness = $this->readiness->check($student, $semester, $class);
        if (! $readiness['is_ready']) {
            throw new InvalidArgumentException('Report card is not ready: '.implode(', ', $readiness['reasons']));
        }

        return DB::transaction(function () use ($student, $semester, $class, $actor): ReportCardVersion {
            $reportCard = ReportCard::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->where('report_type', 'SEMESTER')->lockForUpdate()->first();
            if ($reportCard === null) {
                $reportCard = ReportCard::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'report_type' => 'SEMESTER']);
            }
            $versionNo = ((int) $reportCard->versions()->max('version_no')) + 1;
            $version = ReportCardVersion::create([
                'report_card_id' => $reportCard->id,
                'version_no' => $versionNo,
                'status' => 'DRAFT',
                'source_cutoff_at' => now(),
                'student_name_snapshot' => $student->full_name,
                'class_id' => $class->id,
                'class_name_snapshot' => $class->display_name,
                'semester_name_snapshot' => $semester->display_name,
                'created_by' => $actor->id,
            ]);

            $grades = SemesterSubjectGrade::query()
                ->with('subject')
                ->where('student_id', $student->id)->where('semester_id', $semester->id)
                ->where('workflow_status', 'LOCKED')->whereNotNull('score')->get();
            foreach ($grades as $grade) {
                ReportCardSubjectLine::create([
                    'report_card_version_id' => $version->id,
                    'subject_id' => $grade->subject_id,
                    'subject_name_snapshot' => $grade->subject->subject_name,
                    'semester_subject_grade_id' => $grade->id,
                    'grade_version_no' => $grade->version_no,
                    'score' => $grade->score,
                ]);
            }

            $attendance = StudentAttendance::query()
                ->whereHas('participant', fn ($query) => $query->where('student_id', $student->id)->whereHas('classSession', fn ($session) => $session->where('class_id', $class->id)->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])->whereBetween('planned_start_at', [$semester->starts_on->startOfDay(), $semester->ends_on->endOfDay()])))
                ->whereNotNull('attendance_status')
                ->select('attendance_status', DB::raw('COUNT(*) as status_count'))
                ->groupBy('attendance_status')->get();
            foreach ($attendance as $line) {
                ReportCardAttendanceLine::create(['report_card_version_id' => $version->id, 'status_code' => $line->attendance_status, 'status_count' => $line->status_count]);
            }

            $this->auditLogger->record([
                'actor_user_id' => $actor->id,
                'action' => 'REPORT_CARD_DRAFT_CREATED',
                'entity_type' => ReportCardVersion::class,
                'entity_id' => (string) $version->id,
                'version_after' => $version->version_no,
                'new_values' => ['report_card_id' => (string) $reportCard->id, 'status' => 'DRAFT', 'subject_line_count' => $grades->count(), 'attendance_line_count' => $attendance->count()],
            ]);

            return $version->load(['subjectLines', 'attendanceLines']);
        });
    }
}
