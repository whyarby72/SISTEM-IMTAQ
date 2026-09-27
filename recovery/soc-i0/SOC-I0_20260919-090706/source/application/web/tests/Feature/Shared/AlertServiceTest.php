<?php

namespace Tests\Feature\Shared;

use App\Models\User;
use App\Shared\Platform\Alerts\Models\Alert;
use App\Shared\Platform\Alerts\Models\AlertRule;
use App\Shared\Platform\Alerts\Services\AlertRuleVersionService;
use App\Shared\Platform\Alerts\Services\AlertService;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_identical_alerts_are_deduplicated_and_closed_alerts_are_retained(): void
    {
        $owner = User::factory()->create();
        $rule = AlertRule::create(['rule_code' => 'TEST_DQ', 'version_no' => 1, 'name' => 'Test DQ', 'category' => 'DQ']);
        $service = app(AlertService::class);

        $first = $service->raise($rule, 'student:1', 'HIGH', $owner, ['missing' => true], 'Student', '00000000-0000-0000-0000-000000000001');
        $duplicate = $service->raise($rule, 'student:1', 'HIGH', $owner);
        $closed = $service->transition($first, $owner, 'CLOSED', 'Condition cleared.');
        $second = $service->raise($rule, 'student:1', 'HIGH', $owner);

        $this->assertSame($first->id, $duplicate->id);
        $this->assertSame('CLOSED', $closed->status);
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame(2, Alert::count());
        $this->assertSame(1, $first->fresh()->actions()->count());
        $this->assertSame(2, AuditLog::where('action', 'ALERT_RAISED')->count());
    }

    public function test_non_owner_requires_manager_permission_and_rules_are_immutable(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $rule = AlertRule::create(['rule_code' => 'TEST_IMMUTABLE', 'name' => 'Test immutable', 'category' => 'DQ']);
        $alert = app(AlertService::class)->raise($rule, 'entity:1', 'MEDIUM', $owner);

        $this->expectException(AuthorizationException::class);
        app(AlertService::class)->transition($alert, $other, 'ACKNOWLEDGED');
    }

    public function test_reopening_closed_alert_clears_resolution_metadata_when_no_recurrence_exists(): void
    {
        $owner = User::factory()->create();
        $rule = AlertRule::create(['rule_code' => 'TEST_REOPEN', 'name' => 'Test reopen', 'category' => 'DQ']);
        $service = app(AlertService::class);
        $alert = $service->raise($rule, 'entity:reopen', 'LOW', $owner);
        $closed = $service->transition($alert, $owner, 'CLOSED');
        $reopened = $service->transition($closed, $owner, 'OPEN');

        $this->assertNull($reopened->resolved_at);
        $this->assertNull($reopened->resolved_by);
    }

    public function test_alert_manager_permission_can_transition_and_rule_update_is_blocked(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $role = Role::create(['code' => 'ALERT_MANAGER', 'name' => 'Alert Manager']);
        $permission = Permission::create(['code' => 'platform.alert.manage', 'name' => 'Manage alerts']);
        $role->permissions()->attach($permission);
        UserRoleAssignment::create(['user_id' => $manager->id, 'role_id' => $role->id]);
        $rule = AlertRule::create(['rule_code' => 'TEST_MANAGER', 'name' => 'Test manager', 'category' => 'DQ']);
        $alert = app(AlertService::class)->raise($rule, 'entity:2', 'LOW', $owner);

        $this->assertSame('ACKNOWLEDGED', app(AlertService::class)->transition($alert, $manager, 'ACKNOWLEDGED')->status);
        $this->expectException(LogicException::class);
        $rule->update(['name' => 'Changed']);
    }

    public function test_new_rule_version_supersedes_old_version_for_raising(): void
    {
        $owner = User::factory()->create();
        $rule = AlertRule::create(['rule_code' => 'TEST_VERSION', 'name' => 'Version 1', 'category' => 'DQ']);
        $version = app(AlertRuleVersionService::class)->createVersion($rule, ['name' => 'Version 2']);

        $this->assertSame(2, $version->version_no);
        $this->assertSame($rule->id, $version->supersedes_rule_id);
        $this->assertSame(1, AuditLog::where('action', 'ALERT_RULE_VERSION_CREATED')->count());
        $this->expectException(\InvalidArgumentException::class);
        app(AlertService::class)->raise($rule, 'entity:version', 'LOW', $owner);
    }

    public function test_invalid_alert_transition_is_rejected(): void
    {
        $owner = User::factory()->create();
        $rule = AlertRule::create(['rule_code' => 'TEST_TRANSITION', 'name' => 'Transition', 'category' => 'DQ']);
        $alert = app(AlertService::class)->raise($rule, 'entity:transition', 'LOW', $owner);
        app(AlertService::class)->transition($alert, $owner, 'CLOSED');

        $this->expectException(\InvalidArgumentException::class);
        app(AlertService::class)->transition($alert->fresh(), $owner, 'ACKNOWLEDGED');
    }
}
