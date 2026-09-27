<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['student_id', 'semester_id', 'report_type'];

    protected $attributes = ['report_type' => 'SEMESTER'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ReportCardVersion::class);
    }
}
