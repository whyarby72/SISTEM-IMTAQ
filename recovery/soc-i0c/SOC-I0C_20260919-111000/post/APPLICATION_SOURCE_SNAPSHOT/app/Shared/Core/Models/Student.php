<?php

namespace App\Shared\Core\Models;

use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentClassEnrollment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_code', 'nis', 'nisn', 'full_name', 'arabic_name', 'nickname', 'gender_code',
        'birth_place', 'birth_date', 'entry_year', 'version_no',
    ];

    protected $attributes = [
        'version_no' => 1,
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'entry_year' => 'integer',
            'version_no' => 'integer',
        ];
    }

    public function guardianRelationships(): HasMany
    {
        return $this->hasMany(StudentGuardianRelationship::class);
    }

    public function classEnrollments(): HasMany
    {
        return $this->hasMany(StudentClassEnrollment::class);
    }

    public function sessionParticipants(): HasMany
    {
        return $this->hasMany(SessionStudentParticipant::class);
    }
}
