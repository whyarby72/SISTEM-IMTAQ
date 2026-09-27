<?php

namespace App\Shared\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffOrganizationalAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'staff_id', 'organizational_unit_id', 'assignment_type', 'is_primary',
        'effective_from', 'effective_until', 'source_reference', 'version_no',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'version_no' => 'integer',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }
}
