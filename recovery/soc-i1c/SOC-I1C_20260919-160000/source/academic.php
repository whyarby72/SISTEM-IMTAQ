<?php

use App\Domains\Academic\Semantics\Enums\AttendanceSourceType;

return [
    'session_occurrence_enabled' => (bool) env('ACADEMIC_SESSION_OCCURRENCE_ENABLED', false),
    'attendance_source_precedence' => [
        AttendanceSourceType::LIVE_TRANSACTIONAL->value,
        AttendanceSourceType::LEGACY_MONTHLY_SNAPSHOT->value,
    ],
];
