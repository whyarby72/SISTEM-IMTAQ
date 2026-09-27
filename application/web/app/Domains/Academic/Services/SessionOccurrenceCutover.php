<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Throwable;

class SessionOccurrenceCutover
{
    public const LEGACY = 'LEGACY';

    public const CANONICAL = 'CANONICAL';

    public const UNCONFIGURED = 'UNCONFIGURED';

    public function enabled(): bool
    {
        return (bool) config('academic.session_occurrence_enabled', false);
    }

    public function cutoverAt(): ?Carbon
    {
        $configured = config('academic.session_occurrence_cutover_at');

        if (blank($configured)) {
            return null;
        }

        try {
            return Carbon::parse($configured, config('app.timezone', 'UTC'));
        } catch (Throwable) {
            return null;
        }
    }

    public function hasValidCutover(): bool
    {
        return $this->cutoverAt() !== null;
    }

    public function regime(ClassSession $session): string
    {
        if (! $this->enabled()) {
            return self::LEGACY;
        }

        $cutoverAt = $this->cutoverAt();

        if ($cutoverAt === null) {
            return self::UNCONFIGURED;
        }

        return $session->planned_start_at->greaterThanOrEqualTo($cutoverAt)
            ? self::CANONICAL
            : self::LEGACY;
    }

    public function isCanonical(ClassSession $session): bool
    {
        return $this->regime($session) === self::CANONICAL;
    }

    public function requireCanonicalWrite(ClassSession $session): void
    {
        if (! $this->enabled()) {
            throw new AuthorizationException('Pencatatan kejadian sesi belum diaktifkan.');
        }

        if (! $this->hasValidCutover()) {
            throw new AuthorizationException('Cutover kejadian sesi belum dikonfigurasi secara valid.');
        }

        if (! $this->isCanonical($session)) {
            throw new AuthorizationException('Sesi sebelum cutover tetap menggunakan alur legacy.');
        }
    }
}
