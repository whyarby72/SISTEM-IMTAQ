<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\AcademicYear;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class AcademicCalendarEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'academic_year_id', 'event_type', 'title', 'start_at', 'end_at',
        'organizational_unit_id', 'class_id', 'regular_session_policy',
        'notes', 'workflow_status', 'created_by_user_id', 'updated_by_user_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $event): void {
            if ($event->end_at->lessThanOrEqualTo($event->start_at)) {
                throw new InvalidArgumentException('end_at must be after start_at.');
            }
        });
    }

    protected function casts(): array
    {
        return ['start_at' => 'datetime', 'end_at' => 'datetime'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }
}
