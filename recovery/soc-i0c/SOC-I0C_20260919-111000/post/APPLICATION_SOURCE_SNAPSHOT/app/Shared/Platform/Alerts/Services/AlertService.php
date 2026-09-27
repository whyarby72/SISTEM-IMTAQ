<?php

namespace App\Shared\Platform\Alerts\Services;

use App\Models\User;
use App\Shared\Platform\Alerts\Models\Alert;
use App\Shared\Platform\Alerts\Models\AlertAction;
use App\Shared\Platform\Alerts\Models\AlertRule;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AlertService
{
    private const ACTIVE_STATUSES = ['OPEN', 'ACKNOWLEDGED', 'RESOLVED'];

    private const STATUSES = ['OPEN', 'ACKNOWLEDGED', 'RESOLVED', 'CLOSED'];

    private const TRANSITIONS = [
        'OPEN' => ['ACKNOWLEDGED', 'RESOLVED', 'CLOSED'],
        'ACKNOWLEDGED' => ['OPEN', 'RESOLVED', 'CLOSED'],
        'RESOLVED' => ['OPEN', 'CLOSED'],
        'CLOSED' => ['OPEN'],
    ];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function raise(AlertRule $rule, string $fingerprint, string $severity, User $owner, array $evidence = [], ?string $entityType = null, ?string $entityId = null, $dueAt = null): Alert
    {
        if (! $rule->is_active) {
            throw new InvalidArgumentException('Inactive alert rules cannot raise alerts.');
        }
        $latestVersion = AlertRule::query()->where('rule_code', $rule->rule_code)->max('version_no');
        if ((int) $rule->version_no !== (int) $latestVersion) {
            throw new InvalidArgumentException('Only the latest alert rule version may raise alerts.');
        }

        return DB::transaction(function () use ($rule, $fingerprint, $severity, $owner, $evidence, $entityType, $entityId, $dueAt): Alert {
            $active = Alert::query()->where('alert_rule_id', $rule->id)->where('fingerprint', $fingerprint)->whereIn('status', self::ACTIVE_STATUSES)->lockForUpdate()->first();
            if ($active !== null) {
                if ((string) $active->owner_user_id !== (string) $owner->id) {
                    $previousOwnerId = $active->owner_user_id;
                    $active->update(['owner_user_id' => $owner->id, 'updated_at' => now()]);
                    $this->auditLogger->record(['actor_user_id' => $owner->id, 'action' => 'ALERT_OWNER_REASSIGNED', 'entity_type' => Alert::class, 'entity_id' => (string) $active->id, 'old_values' => ['owner_user_id' => $previousOwnerId], 'new_values' => ['owner_user_id' => $owner->id]]);
                }

                return $active;
            }
            $alert = Alert::create(['alert_rule_id' => $rule->id, 'fingerprint' => $fingerprint, 'dedup_key' => $fingerprint, 'entity_type' => $entityType, 'entity_id' => $entityId, 'owner_user_id' => $owner->id, 'severity' => $severity, 'status' => 'OPEN', 'due_at' => $dueAt, 'evidence' => $evidence]);
            $this->auditLogger->record(['actor_user_id' => $owner->id, 'action' => 'ALERT_RAISED', 'entity_type' => Alert::class, 'entity_id' => (string) $alert->id, 'new_values' => ['status' => 'OPEN', 'rule_id' => (string) $rule->id, 'fingerprint' => $fingerprint]]);

            return $alert;
        });
    }

    public function transition(Alert $alert, User $actor, string $toStatus, ?string $notes = null): Alert
    {
        if ($alert->owner_user_id !== $actor->id && ! $actor->roleAssignments()->effectiveAt()->whereHas('role.permissions', fn ($query) => $query->where('code', 'platform.alert.manage'))->exists()) {
            throw new AuthorizationException('Alert owner or alert manager permission is required.');
        }
        if (! in_array($toStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException('Alert status is not supported.');
        }

        return DB::transaction(function () use ($alert, $actor, $toStatus, $notes): Alert {
            $locked = Alert::query()->whereKey($alert->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $locked->status;
            if (! in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true)) {
                throw new InvalidArgumentException("Alert cannot transition from {$fromStatus} to {$toStatus}.");
            }
            $updates = ['status' => $toStatus, 'updated_at' => now()];
            if ($toStatus === 'OPEN') {
                $updates += ['dedup_key' => $locked->fingerprint, 'resolved_at' => null, 'resolved_by' => null];
            } elseif ($toStatus === 'CLOSED') {
                $updates['dedup_key'] = null;
            }
            if ($toStatus === 'RESOLVED' || $toStatus === 'CLOSED') {
                $updates += ['resolved_at' => $locked->resolved_at ?? now(), 'resolved_by' => $locked->resolved_by ?? $actor->id];
            }
            $locked->update($updates);
            AlertAction::create(['alert_id' => $locked->id, 'actor_user_id' => $actor->id, 'action_type' => strtolower($toStatus), 'from_status' => $fromStatus, 'to_status' => $toStatus, 'notes' => $notes]);
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'ALERT_STATUS_CHANGED', 'entity_type' => Alert::class, 'entity_id' => (string) $locked->id, 'old_values' => ['status' => $fromStatus], 'new_values' => ['status' => $toStatus]]);

            return $locked->fresh();
        });
    }
}
