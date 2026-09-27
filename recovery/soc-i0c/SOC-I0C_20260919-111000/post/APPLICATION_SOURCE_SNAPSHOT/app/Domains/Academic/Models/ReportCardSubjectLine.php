<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ReportCardSubjectLine extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['report_card_version_id', 'subject_id', 'subject_name_snapshot', 'semester_subject_grade_id', 'grade_version_no', 'score'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'grade_version_no' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Report card subject snapshots are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Report card subject snapshots are immutable.'));
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ReportCardVersion::class, 'report_card_version_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(SemesterSubjectGrade::class, 'semester_subject_grade_id');
    }
}
