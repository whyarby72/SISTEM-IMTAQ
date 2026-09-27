<?php

namespace Tests\Feature\Shared;

use App\Shared\Core\Models\Guardian;
use App\Shared\Core\Models\GuardianContactChannel;
use App\Shared\Core\Models\Student;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreGuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardian_identity_relationship_and_contact_tables_have_separate_shapes(): void
    {
        foreach (['guardians', 'student_guardian_relationships', 'guardian_contact_channels'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        $this->assertTrue(Schema::hasColumn('guardians', 'guardian_code'));
        $this->assertTrue(Schema::hasColumn('student_guardian_relationships', 'authorized_for_parent_reports'));
        $this->assertTrue(Schema::hasColumn('guardian_contact_channels', 'verification_status'));
        $this->assertFalse(Schema::hasColumn('guardian_contact_channels', 'communication_consent'));
    }

    public function test_one_guardian_can_have_student_specific_relationships(): void
    {
        $guardian = Guardian::create([
            'guardian_code' => 'GDN-001',
            'full_name' => 'Shared Guardian',
        ]);
        $firstStudent = Student::create(['student_code' => 'IMTAQ-G-001', 'full_name' => 'First Child']);
        $secondStudent = Student::create(['student_code' => 'IMTAQ-G-002', 'full_name' => 'Second Child']);

        $firstStudent->guardianRelationships()->create([
            'guardian_id' => $guardian->id,
            'relationship_type' => 'FATHER',
        ]);
        $secondStudent->guardianRelationships()->create([
            'guardian_id' => $guardian->id,
            'relationship_type' => 'GUARDIAN',
            'authorized_for_parent_reports' => true,
        ]);

        $this->assertCount(2, $guardian->relationships);
        $this->assertTrue($secondStudent->guardianRelationships()->first()->authorized_for_parent_reports);
    }

    public function test_primary_contact_defaults_unverified_and_active_primary_is_unique_per_type(): void
    {
        $guardian = Guardian::create(['guardian_code' => 'GDN-002', 'full_name' => 'Contact Guardian']);
        $channel = GuardianContactChannel::create([
            'guardian_id' => $guardian->id,
            'channel_type' => 'PHONE',
            'normalized_value' => '+621234567890',
            'is_primary' => true,
        ]);

        $this->assertSame('UNVERIFIED', $channel->verification_status);
        $this->expectException(UniqueConstraintViolationException::class);
        GuardianContactChannel::create([
            'guardian_id' => $guardian->id,
            'channel_type' => 'PHONE',
            'normalized_value' => '+629876543210',
            'is_primary' => true,
        ]);
    }
}
