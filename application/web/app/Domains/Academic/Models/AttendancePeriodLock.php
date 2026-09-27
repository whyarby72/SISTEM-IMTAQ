<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePeriodLock extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['class_id', 'period_start', 'period_end', 'status', 'locked_by', 'locked_at', 'version_no'];

    protected $attributes = ['status' => 'OPEN', 'version_no' => 1];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date', 'locked_at' => 'datetime', 'version_no' => 'integer'];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
