<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicTranscript extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['student_id', 'transcript_type'];

    protected $attributes = ['transcript_type' => 'ACADEMIC'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AcademicTranscriptVersion::class);
    }
}
