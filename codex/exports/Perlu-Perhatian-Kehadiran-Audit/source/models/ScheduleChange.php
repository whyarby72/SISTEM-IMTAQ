<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Staff;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleChange extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['change_code', 'change_type', 'source_session_id', 'related_session_id', 'original_teacher_id', 'replacement_teacher_id', 'new_start_at', 'new_end_at', 'reason', 'requested_by_user_id', 'requested_at', 'approved_by_user_id', 'approved_at', 'applied_by_user_id', 'applied_at', 'status'];

    protected function casts(): array
    {
        return ['new_start_at' => 'datetime', 'new_end_at' => 'datetime', 'requested_at' => 'datetime', 'approved_at' => 'datetime', 'applied_at' => 'datetime'];
    }

    public function sourceSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'source_session_id');
    }

    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'original_teacher_id');
    }

    public function replacementTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'replacement_teacher_id');
    }
}
