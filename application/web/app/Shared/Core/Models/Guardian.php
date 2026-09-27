<?php

namespace App\Shared\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guardian extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['guardian_code', 'full_name', 'record_status', 'version_no'];

    protected $attributes = [
        'record_status' => 'ACTIVE',
        'version_no' => 1,
    ];

    protected function casts(): array
    {
        return ['version_no' => 'integer'];
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(StudentGuardianRelationship::class);
    }

    public function contactChannels(): HasMany
    {
        return $this->hasMany(GuardianContactChannel::class);
    }
}
