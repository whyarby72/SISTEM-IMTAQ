<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSourceCertification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'source_type', 'period', 'scope_type', 'scope_key', 'metric', 'source_reference',
        'certification_status', 'certified_by', 'certified_at', 'certification_reason',
        'evidence_reference', 'revoked_by', 'revoked_at', 'revocation_reason',
    ];

    protected function casts(): array
    {
        return ['certified_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function certifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
