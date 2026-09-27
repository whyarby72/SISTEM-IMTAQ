<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicCalendarEvent;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeacherScheduleImporter
{
    private const WEEKDAYS = ['Sunday' => 7, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];

    private const GROUP_CLASS_CODES = [
        'TG-1' => ['IMTAQ-2026-1'],
        'TG-2' => ['IMTAQ-2026-2A'],
        'TG-3' => ['IMTAQ-2026-3A'],
        'TG-B' => ['IMTAQ-2026-2B', 'IMTAQ-2026-3B'],
    ];

    public function import(string $csvPath): array
    {
        return DB::transaction(function () use ($csvPath): array {
            $year = AcademicYear::query()->where('year_code', '2026/2027')->firstOrFail();
            $semester = Semester::query()->where('academic_year_id', $year->id)->where('semester_code', 'S1-2026-2027')->firstOrFail();
            $rows = $this->readCsv($csvPath);
            $imported = 0;
            $excluded = 0;
            $scopeCount = 0;

            foreach ($rows as $row) {
                if ($row['subject_id'] === 'SUB-SPORT') {
                    $excluded++;

                    continue;
                }
                $classCodes = self::GROUP_CLASS_CODES[$row['teaching_group_code']] ?? throw new \RuntimeException('Unknown teaching group: '.$row['teaching_group_code']);
                $classes = AcademicClass::query()->where('academic_year_id', $year->id)->whereIn('class_code', $classCodes)->get()->keyBy('class_code');
                if ($classes->count() !== count($classCodes)) {
                    throw new \RuntimeException('Official class mapping incomplete for '.$row['teaching_group_code']);
                }
                $teacher = Staff::query()->where('staff_code', $row['teacher_code'])->where('record_status', 'ACTIVE')->firstOrFail();
                $subject = Subject::query()->where('subject_code', $row['subject_id'])->where('status', 'ACTIVE')->firstOrFail();
                $primaryClass = $classes->first();
                $assignment = TeachingAssignment::query()->firstOrNew(['assignment_code' => 'TA-'.$row['schedule_rule_key'].'-'.$primaryClass->class_code]);
                $assignmentBefore = $assignment->exists ? $assignment->getAttributes() : [];
                $assignment->fill(['semester_id' => $semester->id, 'class_id' => $primaryClass->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => $semester->starts_on, 'effective_until' => $semester->ends_on, 'workflow_status' => 'DRAFT', 'source_reference' => 'IMTAQ_TEACHER_SCHEDULE_V1.1', 'version_no' => $assignment->exists ? $assignment->version_no + 1 : 1]);
                $assignment->save();
                $this->auditRevision($assignment, $assignmentBefore);
                $rule = ScheduleRule::query()->firstOrNew(['teaching_assignment_id' => $assignment->id]);
                $ruleBefore = $rule->exists ? $rule->getAttributes() : [];
                $rule->fill(['weekday' => self::WEEKDAYS[$row['day_of_week']], 'start_time' => $row['start_time'], 'end_time' => $row['end_time'], 'recurrence_type' => $row['recurrence_type'] === 'WEEKLY' ? 'EVERY_WEEK' : 'WEEK_OF_MONTH', 'effective_from' => $semester->starts_on, 'effective_until' => $semester->ends_on, 'workflow_status' => 'DRAFT', 'version_no' => $rule->exists ? $rule->version_no + 1 : 1]);
                $rule->save();
                $this->auditRevision($rule, $ruleBefore);
                $rule->groups()->delete();
                $rule->groups()->createMany($classes->map(fn (AcademicClass $class): array => ['class_id' => $class->id, 'scope_role' => $classes->count() > 1 ? 'JOINT_SCOPE' : 'TEACHING_SCOPE'])->values()->all());
                $rule->weekNumbers()->delete();
                if ($row['week_of_month_set'] !== '') {
                    $rule->weekNumbers()->createMany(array_map(fn (string $week): array => ['week_no' => (int) $week], explode('|', $row['week_of_month_set'])));
                }
                $imported++;
                $scopeCount += $classes->count();
            }

            $unitId = $year->classes()->where('class_code', 'IMTAQ-2026-1')->value('organizational_unit_id');
            foreach (['2026-08-08', '2026-08-17', '2026-08-30'] as $date) {
                AcademicCalendarEvent::query()->updateOrCreate(
                    ['academic_year_id' => $year->id, 'event_type' => 'CANCELLED_INSTITUTION', 'start_at' => $date.' 00:00:00'],
                    ['title' => 'Pembatalan kegiatan institusi', 'end_at' => $date.' 23:59:59', 'organizational_unit_id' => $unitId, 'regular_session_policy' => 'BLOCK', 'notes' => 'Source: IMTAQ Teacher Schedule v1.1', 'workflow_status' => 'DRAFT']
                );
            }

            return ['imported_rules' => $imported, 'excluded_sport_rules' => $excluded, 'class_scope_rows' => $scopeCount, 'calendar_exceptions' => 3];
        });
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        $headers = array_map(static fn (string $header): string => ltrim($header, "\xEF\xBB\xBF"), fgetcsv($handle));
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $values);
        }
        fclose($handle);

        return $rows;
    }

    private function auditRevision(Model $model, array $before): void
    {
        app(AuditLogger::class)->record([
            'actor_type' => 'IMPORTER',
            'source_channel' => 'IMPORT',
            'action' => $model->wasRecentlyCreated ? 'SCHEDULE_IMPORTED' : 'SCHEDULE_RECONCILED',
            'entity_type' => $model::class,
            'entity_id' => $model->getKey(),
            'version_before' => $before['version_no'] ?? null,
            'version_after' => $model->version_no,
            'old_values' => $before,
            'new_values' => $model->getAttributes(),
            'technical_metadata' => ['source_reference' => 'IMTAQ_TEACHER_SCHEDULE_V1.1'],
        ]);
    }
}
