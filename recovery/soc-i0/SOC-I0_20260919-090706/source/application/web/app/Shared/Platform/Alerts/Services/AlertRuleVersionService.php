<?php

namespace App\Shared\Platform\Alerts\Services;

use App\Shared\Platform\Alerts\Models\AlertRule;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class AlertRuleVersionService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function createVersion(AlertRule $rule, array $attributes = []): AlertRule
    {
        return DB::transaction(function () use ($rule, $attributes): AlertRule {
            $next = ((int) AlertRule::query()->where('rule_code', $rule->rule_code)->max('version_no')) + 1;
            $version = AlertRule::create([
                'rule_code' => $rule->rule_code,
                'version_no' => $next,
                'supersedes_rule_id' => $rule->id,
                'name' => $attributes['name'] ?? $rule->name,
                'category' => $attributes['category'] ?? $rule->category,
                'is_active' => $attributes['is_active'] ?? true,
                'configuration' => $attributes['configuration'] ?? $rule->configuration,
            ]);
            $this->auditLogger->record(['actor_user_id' => null, 'actor_type' => 'SYSTEM', 'action' => 'ALERT_RULE_VERSION_CREATED', 'entity_type' => AlertRule::class, 'entity_id' => (string) $version->id, 'old_values' => ['superseded_rule_id' => (string) $rule->id], 'new_values' => ['rule_code' => $version->rule_code, 'version_no' => $version->version_no, 'is_active' => $version->is_active]]);

            return $version;
        });
    }
}
