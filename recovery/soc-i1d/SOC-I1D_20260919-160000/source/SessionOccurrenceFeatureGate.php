<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use Illuminate\Auth\Access\AuthorizationException;

class SessionOccurrenceFeatureGate
{
    public function __construct(private readonly SessionOccurrenceCutover $cutover) {}

    public function enabled(): bool
    {
        return $this->cutover->enabled();
    }

    public function requireEnabled(): void
    {
        if (! $this->enabled()) {
            throw new AuthorizationException('Pencatatan kejadian sesi belum diaktifkan.');
        }
    }

    public function requireCanonicalWrite(ClassSession $session): void
    {
        $this->cutover->requireCanonicalWrite($session);
    }

    public function isCanonical(ClassSession $session): bool
    {
        return $this->cutover->isCanonical($session);
    }
}
