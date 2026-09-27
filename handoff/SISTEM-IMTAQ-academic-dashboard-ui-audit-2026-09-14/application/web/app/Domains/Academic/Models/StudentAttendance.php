<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_attendance';

    public $timestamps = false;

    protected $fillable = [
        'session_student_participant_id', 'attendance_status', 'reason_code', 'permission_event_id',
        'arrival_at', 'departure_at', 'notes', 'workflow_status', 'version_no', 'entered_by',
        'entered_at', 'finalized_by', 'finalized_at', 'updated_by', 'updated_at',
    ];

    protected $attributes = ['workflow_status' => 'DRAFT', 'version_no' => 1];

    protected function casts(): array
    {
        return [
            'arrival_at' => 'datetime', 'departure_at' => 'datetime', 'entered_at' => 'datetime',
            'finalized_at' => 'datetime', 'updated_at' => 'datetime', 'version_no' => 'integer',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionStudentParticipant::class, 'session_student_participant_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
