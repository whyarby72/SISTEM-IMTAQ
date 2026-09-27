<?php

namespace Tests\Feature\Shared;

use App\Shared\Platform\Imports\Services\MonthlyStudentAttendanceSnapshotValidator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MonthlyStudentAttendanceSnapshotValidatorTest extends TestCase
{
    public function test_validator_preserves_monthly_grain(): void
    {
        $result = app(MonthlyStudentAttendanceSnapshotValidator::class)->validate([
            ['source_record_id' => 'JUL26-001', 'name_indonesia' => 'A', 'name_arabic' => 'ع', 'class_admin' => '1', 'present' => 8, 'permission' => 1, 'sick' => 1, 'absent' => 0, 'eligible' => 10],
        ], new Collection([['student_id' => 'student-1', 'name_indonesia' => 'A', 'name_arabic' => 'ع']]), new Collection([['student_id' => 'student-1', 'class_id' => 'class-1', 'class_admin' => '1']]), '2026-07');

        $this->assertTrue($result['valid']);
        $this->assertSame(1, $result['mapped_count']);
        $this->assertSame(10, $result['rows'][0]['eligible']);
    }

    public function test_validator_rejects_formula_duplicate_and_session_fields(): void
    {
        $rows = [
            ['source_record_id' => 'JUL26-001', 'name_indonesia' => 'A', 'name_arabic' => 'ع', 'class_admin' => '1', 'present' => 8, 'permission' => 1, 'sick' => 1, 'absent' => 0, 'eligible' => 9],
            ['source_record_id' => 'JUL26-001', 'name_indonesia' => 'A', 'name_arabic' => 'ع', 'class_admin' => '1', 'present' => 8, 'permission' => 1, 'sick' => 1, 'absent' => 0, 'eligible' => 10, 'session_id' => 'must-not-exist'],
        ];
        $result = app(MonthlyStudentAttendanceSnapshotValidator::class)->validate($rows, new Collection, new Collection, '2026-07');

        $this->assertFalse($result['valid']);
        $this->assertSame(['ELIGIBLE_FORMULA_MISMATCH', 'DUPLICATE_OR_MISSING_SOURCE_RECORD'], array_column($result['errors'], 'code'));
    }

    public function test_validator_requires_exact_canonical_identity_and_class_mapping(): void
    {
        $result = app(MonthlyStudentAttendanceSnapshotValidator::class)->validate([
            ['source_record_id' => 'JUL26-001', 'name_indonesia' => 'A', 'name_arabic' => 'ع', 'class_admin' => '2A', 'present' => 1, 'permission' => 0, 'sick' => 0, 'absent' => 0, 'eligible' => 1],
        ], new Collection([['student_id' => 'student-1', 'name_indonesia' => 'A', 'name_arabic' => 'ع']]), new Collection([['student_id' => 'student-1', 'class_id' => 'class-1', 'class_admin' => '1']]), '2026-07');

        $this->assertFalse($result['valid']);
        $this->assertSame('CLASS_ENROLLMENT_MAPPING_REQUIRED', $result['errors'][0]['code']);
    }
}
