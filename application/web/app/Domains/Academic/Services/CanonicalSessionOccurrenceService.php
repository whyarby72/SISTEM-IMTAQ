<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionOccurrenceVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CanonicalSessionOccurrenceService
{
    public function __construct(
        private readonly SessionParticipantSnapshotter $snapshotter,
        private readonly SessionOccurrenceCutover $cutover,
    ) {}

    /**
     * Append the first or next routine occurrence observation.
     *
     * @param  array{reason?:?string,is_partial?:bool,partial_reason?:?string,replacement_class_session_id?:?string}  $attributes
     */
    public function record(ClassSession $session, User $actor, string $status, array $attributes = []): SessionOccurrenceVersion
    {
        return DB::transaction(function () use ($session, $actor, $status, $attributes): SessionOccurrenceVersion {
            $locked = ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            $current = $this->effectiveVersion($locked);
            $this->validateObservation($locked, $current, $status, $attributes, false);

            return $this->append($locked, $actor, $status, $attributes, $current);
        });
    }

    /**
     * Append a correction to an existing effective occurrence observation.
     *
     * @param  array{is_partial?:bool,partial_reason?:?string,replacement_class_session_id?:?string}  $attributes
     */
    public function correctEffectiveOccurrence(ClassSession $session, User $actor, string $status, string $reason, array $attributes = []): SessionOccurrenceVersion
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('A correction reason is required.');
        }

        return DB::transaction(function () use ($session, $actor, $status, $reason, $attributes): SessionOccurrenceVersion {
            $locked = ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            $current = $this->effectiveVersion($locked);
            if ($current === null) {
                throw new InvalidArgumentException('An effective occurrence is required before correction.');
            }

            $attributes['reason'] = $reason;
            $this->validateObservation($locked, $current, $status, $attributes, true);

            return $this->append($locked, $actor, $status, $attributes, $current);
        });
    }

    private function effectiveVersion(ClassSession $session): ?SessionOccurrenceVersion
    {
        if ($session->effective_occurrence_version_id === null) {
            return null;
        }

        return SessionOccurrenceVersion::query()->whereKey($session->effective_occurrence_version_id)->firstOrFail();
    }

    private function validateObservation(ClassSession $session, ?SessionOccurrenceVersion $current, string $status, array $attributes, bool $correction): void
    {
        if (! in_array($status, SessionOccurrenceVersion::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid canonical occurrence status.');
        }

        if ($status === 'HELD'
            && $this->cutover->regime($session) === SessionOccurrenceCutover::CANONICAL
            && now()->utc()->lessThan($session->planned_start_at->copy()->utc())) {
            throw new InvalidArgumentException('KBM belum memasuki waktu mulai; pelaksanaan belum dapat dicatat.');
        }

        if (! $correction && $current !== null && in_array($current->occurrence_status, ['HELD', 'CANCELLED', 'RESCHEDULED'], true)) {
            throw new InvalidArgumentException('Terminal occurrence status requires the correction primitive.');
        }

        if (! $correction && $current?->occurrence_status === 'SCHEDULED' && $status === 'SCHEDULED') {
            throw new InvalidArgumentException('SCHEDULED is already the effective occurrence status.');
        }

        $reason = trim((string) ($attributes['reason'] ?? ''));
        $isPartial = (bool) ($attributes['is_partial'] ?? false);
        $partialReason = $attributes['partial_reason'] ?? null;

        if ($status === 'HELD' && $isPartial && trim((string) $partialReason) === '') {
            throw new InvalidArgumentException('A partial HELD occurrence requires partial_reason.');
        }

        if ($status !== 'HELD' && ($isPartial || $partialReason !== null)) {
            throw new InvalidArgumentException('Partial metadata is only valid for HELD.');
        }

        if ($status === 'HELD' && ! $isPartial && $partialReason !== null) {
            throw new InvalidArgumentException('Non-partial HELD cannot retain partial_reason.');
        }

        if (in_array($status, ['CANCELLED', 'RESCHEDULED'], true) && $reason === '') {
            throw new InvalidArgumentException($status.' occurrence requires a reason.');
        }

        if ($status === 'RESCHEDULED') {
            $replacementId = $attributes['replacement_class_session_id'] ?? null;
            if ($replacementId === null) {
                throw new InvalidArgumentException('RESCHEDULED occurrence requires a replacement session.');
            }
            if ((string) $replacementId === (string) $session->id) {
                throw new InvalidArgumentException('A session cannot replace itself.');
            }
            if (! ClassSession::query()->whereKey($replacementId)->exists()) {
                throw new InvalidArgumentException('Replacement session does not exist.');
            }
        } elseif (($attributes['replacement_class_session_id'] ?? null) !== null) {
            throw new InvalidArgumentException('Replacement session is only valid for RESCHEDULED.');
        }

        if ($status === 'CANCELLED' || $status === 'RESCHEDULED') {
            if ($session->studentParticipants()->whereHas('attendance')->exists()) {
                throw new InvalidArgumentException('Occurrence change is not allowed after student attendance exists.');
            }
        }
    }

    private function append(ClassSession $session, User $actor, string $status, array $attributes, ?SessionOccurrenceVersion $current): SessionOccurrenceVersion
    {
        if ($status === 'HELD') {
            $this->snapshotter->ensure($session);
        }

        $versionNo = ((int) $session->occurrenceVersions()->max('version_no')) + 1;
        $version = SessionOccurrenceVersion::create([
            'class_session_id' => $session->id,
            'version_no' => $versionNo,
            'occurrence_status' => $status,
            'recorded_by_user_id' => $actor->id,
            'reason' => $attributes['reason'] ?? null,
            'is_partial' => $status === 'HELD' ? (bool) ($attributes['is_partial'] ?? false) : false,
            'partial_reason' => $status === 'HELD' && ($attributes['is_partial'] ?? false) ? $attributes['partial_reason'] : null,
            'supersedes_version_id' => $current?->id,
            'replacement_class_session_id' => $status === 'RESCHEDULED' ? $attributes['replacement_class_session_id'] : null,
        ]);

        DB::table('class_sessions')->where('id', $session->id)->update([
            'effective_occurrence_version_id' => $version->id,
            'updated_at' => now(),
        ]);

        return $version;
    }
}
