<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSessionGroomingNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['session_student_participant_id', 'discipline_code', 'note_text', 'created_by', 'created_at', 'updated_by', 'updated_at'];

    public $timestamps = false;

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionStudentParticipant::class, 'session_student_participant_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
