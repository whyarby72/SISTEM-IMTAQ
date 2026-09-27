<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Student;
use App\Shared\Platform\Imports\Models\ImportBatch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyStudentAttendanceSnapshot extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'monthly_student_attendance_snapshots';

    protected $fillable = [
        'period', 'student_id', 'class_id', 'class_admin', 'attendance_group', 'matiq_report_class',
        'source_record_id', 'source_checksum', 'import_batch_id', 'import_file_id', 'import_row_id',
        'scheduled_attendance_units_working', 'present', 'permission', 'sick', 'absent', 'eligible',
        'non_eligible', 'non_eligible_reason', 'attendance_rate', 'source_absent_term', 'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_attendance_units_working' => 'integer', 'present' => 'integer', 'permission' => 'integer',
            'sick' => 'integer', 'absent' => 'integer', 'eligible' => 'integer', 'non_eligible' => 'integer',
            'attendance_rate' => 'decimal:6', 'raw_payload' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
