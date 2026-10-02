<?php

namespace App\Shared\Platform\Authorization\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['code', 'name', 'description', 'module', 'required_permission', 'default_enabled', 'system_enabled', 'is_toggleable', 'sort_order'];

    protected function casts(): array
    {
        return ['default_enabled' => 'boolean', 'system_enabled' => 'boolean', 'is_toggleable' => 'boolean', 'sort_order' => 'integer'];
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(UserFeatureOverride::class);
    }
}
