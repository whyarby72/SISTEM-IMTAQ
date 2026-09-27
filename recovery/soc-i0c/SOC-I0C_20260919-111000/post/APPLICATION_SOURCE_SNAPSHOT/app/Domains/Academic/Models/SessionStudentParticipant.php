<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessionStudentParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['class_session_id', 'student_id', 'participant_basis', 'participant_status', 'is_required', 'eligibility_status', 'non_eligible_reason', 'removal_reason', 'removed_by_user_id', 'removed_at'];

    protected $attributes = ['participant_status' => 'EXPECTED', 'is_required' => true];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'removed_at' => 'datetime'];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(StudentAttendance::class);
    }

    public function groomingNote(): HasOne
    {
        return $this->hasOne(StudentSessionGroomingNote::class);
    }
}
