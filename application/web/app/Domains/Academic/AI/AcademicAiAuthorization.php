<?php

namespace App\Domains\Academic\AI;

use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class AcademicAiAuthorization
{
    public function __construct(private readonly AcademicAuthorizationService $authorization) {}

    public function requireReadAccess(User $user): void
    {
        if (! $this->authorization->hasAcademicFullAuthority($user)) {
            throw new AuthorizationException('Akses AI Academic hanya tersedia untuk Waka Akademik.');
        }
    }
}
