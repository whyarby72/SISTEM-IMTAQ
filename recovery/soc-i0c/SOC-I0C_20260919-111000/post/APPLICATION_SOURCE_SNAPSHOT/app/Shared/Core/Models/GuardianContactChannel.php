<?php

namespace App\Shared\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardianContactChannel extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'guardian_id', 'channel_type', 'normalized_value', 'display_value', 'verification_status',
        'is_primary', 'active_from', 'active_until', 'verified_by_user_id', 'verified_at',
        'source_reference', 'version_no',
    ];

    protected $attributes = [
        'verification_status' => 'UNVERIFIED',
        'is_primary' => false,
        'version_no' => 1,
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'active_from' => 'date',
            'active_until' => 'date',
            'verified_at' => 'datetime',
            'version_no' => 'integer',
        ];
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
