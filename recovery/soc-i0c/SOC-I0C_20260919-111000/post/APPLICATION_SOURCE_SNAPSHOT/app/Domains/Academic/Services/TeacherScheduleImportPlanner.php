<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\Staff;

class TeacherScheduleImportPlanner
{
    private const GROUP_CLASS_CODES = [
        'TG-1' => ['IMTAQ-2026-1'],
        'TG-2' => ['IMTAQ-2026-2A'],
        'TG-3' => ['IMTAQ-2026-3A'],
        'TG-B' => ['IMTAQ-2026-2B', 'IMTAQ-2026-3B'],
    ];

    public function plan(iterable $sourceRows, string $yearCode = '2026/2027', string $semesterCode = 'S1-2026-2027'): array
    {
        $year = AcademicYear::query()->where('year_code', $yearCode)->first();
        $semester = $year ? Semester::query()->where('academic_year_id', $year->id)->where('semester_code', $semesterCode)->first() : null;
        $plan = [];
        $blocked = [];

        foreach (collect($sourceRows) as $row) {
            $row = collect($row);
            if ($row->get('subject_id') === 'SUB-SPORT') {
                continue;
            }

            $groupCode = $row->get('teaching_group_code');
            $teacherCode = $row->get('teacher_code');
            $subjectCode = $row->get('subject_id');
            $classCodes = self::GROUP_CLASS_CODES[$groupCode] ?? [];
            $teacher = Staff::query()->where('staff_code', $teacherCode)->where('record_status', 'ACTIVE')->first();
            $subject = Subject::query()->where('subject_code', $subjectCode)->where('status', 'ACTIVE')->first();

            if (! $year || ! $semester || ! $teacher || ! $subject || $classCodes === []) {
                $blocked[] = [
                    'schedule_rule_key' => $row->get('schedule_rule_key'),
                    'reasons' => array_values(array_filter([
                        ! $year || ! $semester ? 'OFFICIAL_PERIOD_NOT_FOUND' : null,
                        ! $teacher ? 'TEACHER_NOT_FOUND_OR_INACTIVE' : null,
                        ! $subject ? 'SUBJECT_NOT_FOUND_OR_INACTIVE' : null,
                        $classCodes === [] ? 'TEACHING_GROUP_MAPPING_NOT_FOUND' : null,
                    ])),
                ];

                continue;
            }

            foreach (AcademicClass::query()->where('academic_year_id', $year->id)->whereIn('class_code', $classCodes)->get() as $class) {
                $plan[] = [
                    'schedule_rule_key' => $row->get('schedule_rule_key'),
                    'assignment_code' => 'TA-'.$row->get('schedule_rule_key').'-'.$class->class_code,
                    'academic_year_id' => $year->id,
                    'semester_id' => $semester->id,
                    'class_id' => $class->id,
                    'class_code' => $class->class_code,
                    'teacher_staff_id' => $teacher->id,
                    'teacher_code' => $teacher->staff_code,
                    'subject_id' => $subject->id,
                    'subject_code' => $subject->subject_code,
                ];
            }
        }

        return ['plan' => $plan, 'blocked' => $blocked];
    }
}
