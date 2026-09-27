<?php

namespace Tests\Feature\Shared;

use App\Shared\Core\Models\Student;
use App\Shared\Core\Models\StudentStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class CoreStudentStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_history_has_effective_interval_and_audit_context_fields(): void
    {
        $this->assertTrue(Schema::hasTable('student_status_history'));

        foreach ([
            'id', 'student_id', 'status', 'effective_from', 'effective_until',
            'decision_reference', 'reason', 'actor_user_id', 'version_no', 'created_at', 'updated_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('student_status_history', $column));
        }
    }

    public function test_non_overlapping_intervals_are_retained_and_open_ended_interval_is_supported(): void
    {
        $student = Student::create([
            'student_code' => 'IMTAQ-STATUS-001',
            'full_name' => 'Status Student',
        ]);

        $first = StudentStatusHistory::create([
            'student_id' => $student->id,
            'status' => 'ACTIVE',
            'effective_from' => '2026-01-01',
            'effective_until' => '2026-07-01',
            'decision_reference' => 'DEC-001',
        ]);
        $second = StudentStatusHistory::create([
            'student_id' => $student->id,
            'status' => 'GRADUATED',
            'effective_from' => '2026-07-01',
        ]);

        $this->assertSame(1, $first->version_no);
        $this->assertNull($second->effective_until);
        $this->assertSame($student->id, $second->student->id);
    }

    public function test_overlapping_or_invalid_intervals_are_rejected(): void
    {
        $student = Student::create([
            'student_code' => 'IMTAQ-STATUS-002',
            'full_name' => 'Overlap Student',
        ]);

        StudentStatusHistory::create([
            'student_id' => $student->id,
            'status' => 'ACTIVE',
            'effective_from' => '2026-01-01',
            'effective_until' => '2026-07-01',
        ]);

        $this->expectException(InvalidArgumentException::class);
        StudentStatusHistory::create([
            'student_id' => $student->id,
            'status' => 'WITHDRAWN',
            'effective_from' => '2026-06-30',
            'effective_until' => '2026-08-01',
        ]);
    }
}
