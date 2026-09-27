<?php

use App\Domains\Academic\Semantics\Enums\AttendanceSourceType;

return [
    'attendance_source_precedence' => [
        AttendanceSourceType::LIVE_TRANSACTIONAL->value,
        AttendanceSourceType::LEGACY_MONTHLY_SNAPSHOT->value,
    ],
];
