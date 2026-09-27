<?php

namespace Tests\Feature\Shared;

use App\Models\User;
use App\Shared\Platform\Audit\Models\CorrectionRequest;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Audit\Services\CorrectionRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class CoreAuditCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_and_correction_tables_have_required_governance_fields(): void
    {
        foreach (['audit_logs', 'correction_requests'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        foreach (['occurred_at', 'actor_type', 'action', 'entity_type', 'entity_id', 'old_values', 'new_values', 'source_channel'] as $column) {
            $this->assertTrue(Schema::hasColumn('audit_logs', $column));
        }
        foreach (['requested_by_user_id', 'entity_type', 'entity_id', 'status', 'requested_changes', 'reason'] as $column) {
            $this->assertTrue(Schema::hasColumn('correction_requests', $column));
        }
    }

    public function test_audit_log_is_append_only_and_correction_request_starts_pending(): void
    {
        $user = User::factory()->create();
        $audit = app(AuditLogger::class)->record([
            'actor_user_id' => $user->id,
            'action' => 'STUDENT_IDENTITY_UPDATED',
            'entity_type' => 'Student',
            'entity_id' => 'student-1',
            'new_values' => ['full_name' => 'Updated Name'],
        ]);

        $this->assertNotEmpty($audit->id);
        $this->expectException(LogicException::class);
        $audit->update(['reason' => 'attempted rewrite']);
    }

    public function test_correction_request_records_intent_without_applying_business_change(): void
    {
        $user = User::factory()->create();
        $request = app(CorrectionRequestService::class)->submit([
            'requested_by_user_id' => $user->id,
            'entity_type' => 'StudentIdentifier',
            'entity_id' => 'identifier-1',
            'correction_type' => 'VALUE_CORRECTION',
            'requested_changes' => ['identifier_value' => 'NEW-VALUE'],
            'reason' => 'Source document correction',
        ]);

        $this->assertInstanceOf(CorrectionRequest::class, $request);
        $this->assertSame('PENDING', $request->status);
        $this->assertNull($request->applied_at);
        $this->assertDatabaseHas('correction_requests', ['id' => $request->id, 'status' => 'PENDING']);
    }
}
