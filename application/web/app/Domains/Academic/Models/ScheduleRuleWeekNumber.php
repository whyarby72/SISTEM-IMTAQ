<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRuleWeekNumber extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'schedule_rule_week_numbers';

    protected $fillable = ['schedule_rule_id', 'week_no'];

    public function scheduleRule(): BelongsTo
    {
        return $this->belongsTo(ScheduleRule::class);
    }
}
