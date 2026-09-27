<?php

namespace Tests\Feature\Shared;

use App\Shared\Core\Models\Student;
use App\Shared\Core\Models\StudentIdentifier;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreStudentIdentifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_identifier_table_has_versioned_nis_and_nisn_fields(): void
    {
        $this->assertTrue(Schema::hasTable('student_identifiers'));

        foreach ([
            'id', 'student_id', 'identifier_type', 'identifier_value', 'identifier_scope',
            'verification_status', 'record_status', 'valid_from', 'valid_until',
            'source_reference', 'version_no', 'created_at', 'updated_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('student_identifiers', $column));
        }
    }

    public function test_active_nisn_is_unique_but_superseded_value_can_be_retained(): void
    {
        $student = Student::create([
            'student_code' => 'IMTAQ-IDENT-001',
            'full_name' => 'Identifier Student',
        ]);

        $active = StudentIdentifier::create([
            'student_id' => $student->id,
            'identifier_type' => 'NISN',
            'identifier_value' => '1234567890',
        ]);
        $this->assertSame(1, $active->version_no);

        $superseded = StudentIdentifier::create([
            'student_id' => $student->id,
            'identifier_type' => 'NISN',
            'identifier_value' => '1234567890',
            'record_status' => 'SUPERSEDED',
            'version_no' => 2,
        ]);
        $this->assertSame('SUPERSEDED', $superseded->record_status);
        $this->assertSame($student->id, $superseded->student->id);

        $otherStudent = Student::create([
            'student_code' => 'IMTAQ-IDENT-002',
            'full_name' => 'Other Identifier Student',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);
        StudentIdentifier::create([
            'student_id' => $otherStudent->id,
            'identifier_type' => 'NISN',
            'identifier_value' => '1234567890',
        ]);
    }

    public function test_one_active_identifier_per_student_type_and_scope_is_enforced(): void
    {
        $student = Student::create([
            'student_code' => 'IMTAQ-IDENT-003',
            'full_name' => 'Scoped Identifier Student',
        ]);

        StudentIdentifier::create([
            'student_id' => $student->id,
            'identifier_type' => 'NIS',
            'identifier_value' => 'NIS-001',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);
        StudentIdentifier::create([
            'student_id' => $student->id,
            'identifier_type' => 'NIS',
            'identifier_value' => 'NIS-002',
        ]);
    }
}
