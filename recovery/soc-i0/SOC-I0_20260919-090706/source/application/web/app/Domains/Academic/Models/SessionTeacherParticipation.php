<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Staff;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionTeacherParticipation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['class_session_id', 'teacher_staff_id', 'role', 'obligation_type', 'participation_status', 'attendance_status', 'reason', 'schedule_change_id', 'checkin_at', 'checkout_at', 'notes'];

    protected $attributes = ['participation_status' => 'EXPECTED'];

    protected function casts(): array
    {
        return ['checkin_at' => 'datetime', 'checkout_at' => 'datetime'];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_staff_id');
    }
}
