<?php

namespace App\Domains\Academic\Services;

use Illuminate\Auth\Access\AuthorizationException;

class SessionOccurrenceFeatureGate
{
    public function enabled(): bool
    {
        return (bool) config('academic.session_occurrence_enabled', false);
    }

    public function requireEnabled(): void
    {
        if (! $this->enabled()) {
            throw new AuthorizationException('Pencatatan kejadian sesi belum diaktifkan.');
        }
    }
}
