<?php

namespace App\Shared\Platform\Alerts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class AlertRule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['rule_code', 'version_no', 'supersedes_rule_id', 'name', 'category', 'is_active', 'configuration'];

    protected $attributes = ['version_no' => 1, 'is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'configuration' => 'array', 'version_no' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Alert rules are immutable; create a new version.'));
        static::deleting(fn (): never => throw new LogicException('Alert rules are immutable; create a new version.'));
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_rule_id');
    }
}
