<?php

namespace App\Shared\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalUnit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'organizational_units';

    protected $fillable = [
        'unit_code', 'unit_name', 'unit_type', 'parent_unit_id',
        'effective_from', 'effective_until', 'record_status', 'version_no',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'version_no' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_unit_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_unit_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffOrganizationalAssignment::class);
    }
}
