<?php

namespace App\Shared\Platform\Authorization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserRoleAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'role_id', 'scope_type', 'scope_key', 'effective_from',
        'effective_until', 'assignment_reason', 'version_no',
    ];

    protected $attributes = [
        'scope_type' => 'INSTITUTION',
        'version_no' => 1,
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'version_no' => 'integer',
        ];
    }

    public function scopeEffectiveAt(Builder $query, ?Carbon $at = null): Builder
    {
        $date = ($at ?? now())->toDateString();

        return $query
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date);
            })
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date);
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
