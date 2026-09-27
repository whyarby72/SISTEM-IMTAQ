<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\AcademicYear;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['academic_year_id', 'semester_code', 'display_name', 'sequence_no', 'starts_on', 'ends_on', 'status', 'version_no'];

    protected $attributes = ['status' => 'ACTIVE', 'version_no' => 1];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'sequence_no' => 'integer', 'version_no' => 'integer'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
