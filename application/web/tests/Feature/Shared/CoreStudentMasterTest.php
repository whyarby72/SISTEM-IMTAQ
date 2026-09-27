<?php

namespace Tests\Feature\Shared;

use App\Shared\Core\Models\Student;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreStudentMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_master_has_canonical_identity_fields_without_domain_assignment_fields(): void
    {
        $this->assertTrue(Schema::hasTable('students'));

        foreach ([
            'id', 'student_code', 'full_name', 'arabic_name', 'nickname', 'gender_code',
            'birth_place', 'birth_date', 'entry_year', 'version_no', 'created_at', 'updated_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('students', $column));
        }

        foreach (['current_class_id', 'halaqah_id', 'room_id', 'lifecycle_status'] as $forbidden) {
            $this->assertFalse(Schema::hasColumn('students', $forbidden));
        }
    }

    public function test_student_code_is_permanent_and_unique(): void
    {
        $first = Student::create([
            'student_code' => 'IMTAQ-0001',
            'full_name' => 'First Student',
        ]);

        $this->assertNotEmpty($first->id);
        $this->assertSame(1, $first->version_no);

        $this->expectException(UniqueConstraintViolationException::class);
        Student::create([
            'student_code' => 'IMTAQ-0001',
            'full_name' => 'Duplicate Student',
        ]);
    }
}
