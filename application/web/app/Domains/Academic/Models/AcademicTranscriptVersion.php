<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class AcademicTranscriptVersion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['academic_transcript_id', 'version_no', 'status', 'source_cutoff_at', 'student_name_snapshot', 'created_by', 'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at', 'published_by', 'published_at'];

    protected function casts(): array
    {
        return ['source_cutoff_at' => 'datetime', 'reviewed_at' => 'datetime', 'approved_at' => 'datetime', 'published_at' => 'datetime', 'version_no' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Transcript versions are append-only.'));
        static::deleting(fn (): never => throw new LogicException('Transcript versions are append-only.'));
    }

    public function transcript(): BelongsTo
    {
        return $this->belongsTo(AcademicTranscript::class, 'academic_transcript_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AcademicTranscriptLine::class);
    }

    public function signatories(): HasMany
    {
        return $this->hasMany(AcademicTranscriptSignatory::class, 'academic_transcript_version_id');
    }
}
