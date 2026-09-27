<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CONSTRAINTS = [
        'class_sessions' => [
            'chk_class_sessions_session_status' => "session_status IN ('PLANNED','CONFIRMED','COMPLETED','CANCELLED','RESCHEDULED')",
            'chk_class_sessions_session_source' => "session_source IN ('SCHEDULED','EXTRA','RESCHEDULED','AD_HOC')",
            'chk_class_sessions_participant_scope' => "participant_scope IN ('FULL_CLASS','SELECTED_STUDENTS')",
        ],
        'session_teacher_participations' => [
            'chk_session_teacher_participations_role' => "role IN ('PRIMARY','SUBSTITUTE')",
            'chk_session_teacher_participations_obligation_type' => "obligation_type IN ('TEACHING_ASSIGNMENT','REPLACEMENT')",
            'chk_session_teacher_participations_participation_status' => "participation_status IN ('EXPECTED')",
            'chk_session_teacher_participations_attendance_status' => "attendance_status IS NULL OR attendance_status IN ('PRESENT','ABSENT','SICK','IZIN','OTHER')",
        ],
        'student_attendance' => [
            'chk_student_attendance_attendance_status' => "attendance_status IS NULL OR attendance_status IN ('PRESENT','ABSENT','SICK','IZIN','LATE','EXCUSED')",
            'chk_student_attendance_workflow_status' => "workflow_status IN ('DRAFT','VALIDATED')",
        ],
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::CONSTRAINTS as $table => $constraints) {
            foreach ($constraints as $name => $expression) {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (array_reverse(self::CONSTRAINTS, true) as $table => $constraints) {
            foreach (array_reverse(array_keys($constraints)) as $name) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}");
            }
        }
    }
};
