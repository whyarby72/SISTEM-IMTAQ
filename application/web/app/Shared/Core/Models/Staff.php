<?php

namespace App\Shared\Core\Models;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'staff_code', 'full_name', 'record_status', 'active_from', 'active_until', 'version_no',
    ];

    protected function casts(): array
    {
        return [
            'active_from' => 'date',
            'active_until' => 'date',
            'version_no' => 'integer',
        ];
    }

    public function organizationalAssignments(): HasMany
    {
        return $this->hasMany(StaffOrganizationalAssignment::class);
    }

    public function homeroomAssignments(): HasMany
    {
        return $this->hasMany(ClassHomeroomAssignment::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_staff_id');
    }

    public function userLink(): HasOne
    {
        return $this->hasOne(UserStaffLink::class);
    }
}
