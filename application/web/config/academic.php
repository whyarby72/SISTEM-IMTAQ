<?php

use App\Domains\Academic\Semantics\Enums\AttendanceSourceType;

return [
    'business_timezone' => env('ACADEMIC_BUSINESS_TIMEZONE', 'Asia/Jakarta'),
    'session_occurrence_enabled' => (bool) env('ACADEMIC_SESSION_OCCURRENCE_ENABLED', false),
    'session_occurrence_cutover_at' => env('ACADEMIC_SESSION_OCCURRENCE_CUTOVER_AT'),
    'attendance_source_precedence' => [
        AttendanceSourceType::LIVE_TRANSACTIONAL->value,
        AttendanceSourceType::LEGACY_MONTHLY_SNAPSHOT->value,
    ],
    'ai' => [
        'max_tool_rounds' => (int) env('ACADEMIC_AI_MAX_TOOL_ROUNDS', 4),
        'assistant_enabled' => (bool) env('ACADEMIC_AI_ASSISTANT_ENABLED', false),
        'question_max_length' => (int) env('ACADEMIC_AI_QUESTION_MAX_LENGTH', 4000),
        'rate_limit_per_minute' => (int) env('ACADEMIC_AI_RATE_LIMIT_PER_MINUTE', 10),
        'max_output_tokens' => (int) env('ACADEMIC_AI_MAX_OUTPUT_TOKENS', 800),
    ],
];
