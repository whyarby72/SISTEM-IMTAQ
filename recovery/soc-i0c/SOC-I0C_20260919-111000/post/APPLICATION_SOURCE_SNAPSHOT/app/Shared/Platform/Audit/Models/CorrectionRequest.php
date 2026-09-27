<?php

namespace App\Shared\Platform\Audit\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'requested_by_user_id', 'entity_type', 'entity_id', 'correction_type', 'status',
        'requested_changes', 'reason', 'reviewed_by_user_id', 'reviewed_at', 'applied_at',
        'rejection_reason', 'version_no',
    ];

    protected $attributes = [
        'status' => 'PENDING',
        'version_no' => 1,
    ];

    protected function casts(): array
    {
        return [
            'requested_changes' => 'array',
            'reviewed_at' => 'datetime',
            'applied_at' => 'datetime',
            'version_no' => 'integer',
        ];
    }
}
