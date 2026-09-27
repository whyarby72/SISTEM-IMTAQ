<?php

namespace Tests\Feature\Shared;

use App\Models\User;
use App\Shared\Core\Models\StudentGuardianRelationship;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CoreContractsTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_foreign_keys_block_orphan_guardian_relationships(): void
    {
        $this->expectException(QueryException::class);

        StudentGuardianRelationship::create([
            'student_id' => '00000000-0000-0000-0000-000000000001',
            'guardian_id' => '00000000-0000-0000-0000-000000000002',
            'relationship_type' => 'PARENT',
            'effective_from' => '2026-01-01',
        ]);
    }

    public function test_expired_role_assignment_is_not_effective(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'CONTRACT_VIEWER', 'name' => 'Contract Viewer']);

        UserRoleAssignment::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => 'INSTITUTION',
            'effective_from' => '2025-01-01',
            'effective_until' => '2026-01-01',
        ]);

        $this->assertFalse($user->roleAssignments()->effectiveAt(Carbon::parse('2026-01-01'))->exists());
    }

    public function test_audit_payload_redacts_nested_credential_like_values(): void
    {
        $audit = app(AuditLogger::class)->record([
            'action' => 'CORE_CONTRACT_TEST',
            'entity_type' => 'Student',
            'entity_id' => 'student-1',
            'new_values' => [
                'full_name' => 'Safe Name',
                'api_key' => 'do-not-store',
                'nested' => ['access_token' => 'also-do-not-store'],
            ],
        ]);

        $this->assertSame('Safe Name', $audit->new_values['full_name']);
        $this->assertSame('[REDACTED]', $audit->new_values['api_key']);
        $this->assertSame('[REDACTED]', $audit->new_values['nested']['access_token']);
        $this->assertDatabaseMissing('audit_logs', ['new_values' => '%do-not-store%']);
    }
}
