<?php

namespace App\Shared\Core\Models;

use App\Domains\Academic\Models\AcademicCalendarEvent;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\Semester;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['year_code', 'display_name', 'starts_on', 'ends_on', 'status', 'version_no'];

    protected $attributes = ['status' => 'ACTIVE', 'version_no' => 1];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'version_no' => 'integer'];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(AcademicClass::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(AcademicCalendarEvent::class);
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }
}
