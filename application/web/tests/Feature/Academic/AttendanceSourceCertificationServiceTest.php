<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Services\AttendanceSourceCertificationService;
use App\Models\User;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSourceCertificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_certification_requires_explicit_permission_and_writes_audit(): void
    {
        $actor = User::factory()->create();
        $this->expectException(AuthorizationException::class);
        app(AttendanceSourceCertificationService::class)->certify($actor, $this->payload());
    }

    public function test_authorized_certification_is_explicit_and_audited(): void
    {
        $actor = $this->authorizedActor();
        $certification = app(AttendanceSourceCertificationService::class)->certify($actor, $this->payload());

        $this->assertSame('CERTIFIED', $certification->certification_status);
        $this->assertSame(1, AuditLog::where('action', 'ATTENDANCE_SOURCE_CERTIFIED')->count());
        $this->assertSame('CERTIFIED', app(AttendanceSourceCertificationService::class)->getCertificationStatus('LEGACY_MONTHLY_SNAPSHOT', '2026-07', 'INSTITUTION', 'INSTITUTION')['certification_status']);
    }

    public function test_revoked_certification_is_no_longer_certified(): void
    {
        $actor = $this->authorizedActor();
        $certification = app(AttendanceSourceCertificationService::class)->certify($actor, $this->payload());

        $revoked = app(AttendanceSourceCertificationService::class)->revoke($actor, (string) $certification->id, 'Reconciliation evidence changed.');

        $this->assertSame('REVOKED', $revoked->certification_status);
        $this->assertSame(1, AuditLog::where('action', 'ATTENDANCE_SOURCE_CERTIFICATION_REVOKED')->count());
    }

    private function authorizedActor(): User
    {
        $actor = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        $permission = Permission::create(['code' => AttendanceSourceCertificationService::CERTIFY_PERMISSION, 'name' => 'Certify attendance source']);
        $role->permissions()->attach($permission);
        UserRoleAssignment::create(['user_id' => $actor->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        return $actor;
    }

    private function payload(): array
    {
        return [
            'source_type' => 'LEGACY_MONTHLY_SNAPSHOT',
            'period' => '2026-07',
            'scope_type' => 'INSTITUTION',
            'scope_key' => 'INSTITUTION',
            'metric' => 'ATTENDANCE',
            'source_reference' => 'IMTAQ-JUL-2026-MONTHLY',
            'certification_reason' => 'Human review completed.',
            'evidence_reference' => 'DOSSIER-2026-07-001',
        ];
    }
}
