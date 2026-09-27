<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use App\Shared\Platform\Imports\Models\ImportBatch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyAttendanceSummary extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'monthly_attendance_summaries';

    protected $fillable = ['period', 'class_id', 'attendance_group', 'matiq_report_class', 'roster', 'present', 'permission', 'sick', 'absent', 'eligible', 'non_eligible', 'attendance_rate', 'non_eligible_reason', 'import_batch_id', 'source_checksum', 'status', 'published_by_user_id', 'published_at'];

    protected function casts(): array
    {
        return ['roster' => 'integer', 'present' => 'integer', 'permission' => 'integer', 'sick' => 'integer', 'absent' => 'integer', 'eligible' => 'integer', 'non_eligible' => 'integer', 'attendance_rate' => 'decimal:6', 'published_at' => 'datetime'];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }
}
