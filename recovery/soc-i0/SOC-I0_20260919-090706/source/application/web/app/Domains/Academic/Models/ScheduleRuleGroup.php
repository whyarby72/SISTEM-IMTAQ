<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRuleGroup extends Model
{
    use HasUuids;

    protected $fillable = ['schedule_rule_id', 'class_id', 'scope_role'];

    public function scheduleRule(): BelongsTo
    {
        return $this->belongsTo(ScheduleRule::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }
}
