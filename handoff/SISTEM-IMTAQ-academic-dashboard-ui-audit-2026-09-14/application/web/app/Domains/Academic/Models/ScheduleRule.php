<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class ScheduleRule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['teaching_assignment_id', 'weekday', 'start_time', 'end_time', 'recurrence_type', 'location_id', 'effective_from', 'effective_until', 'workflow_status', 'version_no'];

    protected $attributes = ['version_no' => 1];

    protected static function booted(): void
    {
        static::saving(function (self $rule): void {
            if ($rule->weekday < 1 || $rule->weekday > 7) {
                throw new InvalidArgumentException('weekday must be between 1 and 7.');
            }
            if (strtotime($rule->end_time) <= strtotime($rule->start_time)) {
                throw new InvalidArgumentException('end_time must be after start_time.');
            }
            if ($rule->effective_until !== null && $rule->effective_until->lessThanOrEqualTo($rule->effective_from)) {
                throw new InvalidArgumentException('effective_until must be after effective_from.');
            }
        });
    }

    protected function casts(): array
    {
        return ['weekday' => 'integer', 'effective_from' => 'date', 'effective_until' => 'date', 'version_no' => 'integer'];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function weekNumbers(): HasMany
    {
        return $this->hasMany(ScheduleRuleWeekNumber::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(ScheduleRuleGroup::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }
}
