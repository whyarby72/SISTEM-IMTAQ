<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\OrganizationalUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeLevel extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['organizational_unit_id', 'level_code', 'display_name', 'sequence_no', 'status', 'version_no'];

    protected $attributes = ['status' => 'ACTIVE', 'version_no' => 1];

    protected function casts(): array
    {
        return ['sequence_no' => 'integer', 'version_no' => 'integer'];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(AcademicClass::class);
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }
}
