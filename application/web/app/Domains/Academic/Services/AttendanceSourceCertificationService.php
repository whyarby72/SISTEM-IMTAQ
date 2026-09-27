<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AttendanceSourceCertification;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSourceCertificationService
{
    public const CERTIFY_PERMISSION = 'academic.attendance.source_certify';

    public function __construct(
        private readonly AcademicAuthorizationService $authorization,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function prepareCertification(array $payload): array
    {
        foreach (['source_type', 'period', 'scope_type', 'scope_key', 'metric', 'source_reference', 'certification_reason', 'evidence_reference'] as $field) {
            if (blank($payload[$field] ?? null)) {
                throw ValidationException::withMessages([$field => 'Certification evidence is required.']);
            }
        }

        return [
            'status' => 'PENDING_HUMAN_APPROVAL',
            'source_type' => $payload['source_type'],
            'period' => $payload['period'],
            'scope' => ['type' => $payload['scope_type'], 'key' => $payload['scope_key']],
            'metric' => $payload['metric'],
            'source_reference' => $payload['source_reference'],
            'evidence_reference' => $payload['evidence_reference'],
            'certification_reason' => $payload['certification_reason'],
        ];
    }

    public function certify(User $actor, array $payload): AttendanceSourceCertification
    {
        $prepared = $this->prepareCertification($payload);
        if (! $this->authorization->hasEffectivePermission($actor, self::CERTIFY_PERMISSION)) {
            throw new AuthorizationException('Attendance source certification permission is required.');
        }

        return DB::transaction(function () use ($actor, $prepared): AttendanceSourceCertification {
            $certification = AttendanceSourceCertification::create([
                'source_type' => $prepared['source_type'],
                'period' => $prepared['period'],
                'scope_type' => $prepared['scope']['type'],
                'scope_key' => $prepared['scope']['key'],
                'metric' => $prepared['metric'],
                'source_reference' => $prepared['source_reference'],
                'certification_status' => 'CERTIFIED',
                'certified_by' => $actor->id,
                'certified_at' => now(),
                'certification_reason' => $prepared['certification_reason'],
                'evidence_reference' => $prepared['evidence_reference'],
            ]);

            $this->auditLogger->record([
                'actor_user_id' => $actor->id,
                'action' => 'ATTENDANCE_SOURCE_CERTIFIED',
                'entity_type' => AttendanceSourceCertification::class,
                'entity_id' => (string) $certification->id,
                'new_values' => ['source_type' => $certification->source_type, 'period' => $certification->period, 'scope_key' => $certification->scope_key, 'status' => 'CERTIFIED', 'evidence_reference' => $certification->evidence_reference],
                'reason' => $certification->certification_reason,
            ]);

            return $certification;
        });
    }

    public function revoke(User $actor, string $id, string $reason): AttendanceSourceCertification
    {
        if (! $this->authorization->hasEffectivePermission($actor, self::CERTIFY_PERMISSION)) {
            throw new AuthorizationException('Attendance source certification permission is required.');
        }
        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Revocation reason is required.']);
        }

        $certification = AttendanceSourceCertification::query()->find($id);
        if ($certification === null) {
            throw (new ModelNotFoundException)->setModel(AttendanceSourceCertification::class, [$id]);
        }
        if ($certification->certification_status !== 'CERTIFIED') {
            return $certification;
        }

        $certification->forceFill([
            'certification_status' => 'REVOKED',
            'revoked_by' => $actor->id,
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ])->save();
        $this->auditLogger->record([
            'actor_user_id' => $actor->id,
            'action' => 'ATTENDANCE_SOURCE_CERTIFICATION_REVOKED',
            'entity_type' => AttendanceSourceCertification::class,
            'entity_id' => (string) $certification->id,
            'old_values' => ['status' => 'CERTIFIED'],
            'new_values' => ['status' => 'REVOKED'],
            'reason' => $reason,
        ]);

        return $certification->refresh();
    }

    public function getCertificationStatus(string $sourceType, string $period, string $scopeType, string $scopeKey, string $metric = 'ATTENDANCE'): ?AttendanceSourceCertification
    {
        return AttendanceSourceCertification::query()
            ->where('source_type', $sourceType)
            ->where('period', $period)
            ->where('scope_type', $scopeType)
            ->where('scope_key', $scopeKey)
            ->where('metric', $metric)
            ->latest('created_at')
            ->first();
    }
}
