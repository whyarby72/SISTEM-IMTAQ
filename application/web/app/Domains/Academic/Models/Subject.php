<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['subject_code', 'subject_name', 'status', 'version_no'];

    protected $attributes = ['status' => 'ACTIVE', 'version_no' => 1];

    protected function casts(): array
    {
        return ['version_no' => 'integer'];
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
