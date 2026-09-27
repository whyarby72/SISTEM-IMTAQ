<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class ReportCardVersion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['report_card_id', 'version_no', 'status', 'source_cutoff_at', 'student_name_snapshot', 'class_id', 'class_name_snapshot', 'semester_name_snapshot', 'created_by', 'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at', 'published_by', 'published_at'];

    protected function casts(): array
    {
        return ['source_cutoff_at' => 'datetime', 'reviewed_at' => 'datetime', 'approved_at' => 'datetime', 'published_at' => 'datetime', 'version_no' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Report card versions are append-only.'));
        static::deleting(fn (): never => throw new LogicException('Report card versions are append-only.'));
    }

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subjectLines(): HasMany
    {
        return $this->hasMany(ReportCardSubjectLine::class);
    }

    public function attendanceLines(): HasMany
    {
        return $this->hasMany(ReportCardAttendanceLine::class);
    }

    public function signatories(): HasMany
    {
        return $this->hasMany(ReportCardSignatory::class);
    }
}
