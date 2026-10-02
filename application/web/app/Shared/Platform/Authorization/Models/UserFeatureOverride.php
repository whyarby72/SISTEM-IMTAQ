<?php

namespace App\Shared\Platform\Authorization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeatureOverride extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['user_id', 'feature_id', 'state', 'reason', 'changed_by_user_id', 'version_no'];

    protected function casts(): array
    {
        return ['version_no' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
