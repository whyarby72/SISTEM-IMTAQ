<?php

namespace App\Shared\Platform\Alerts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['alert_rule_id', 'fingerprint', 'dedup_key', 'entity_type', 'entity_id', 'owner_user_id', 'severity', 'status', 'due_at', 'resolved_at', 'resolved_by', 'evidence'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'resolved_at' => 'datetime', 'evidence' => 'array'];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'alert_rule_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(AlertAction::class);
    }
}
