<?php

namespace App\Shared\Platform\Authorization\Models;

use App\Models\User;
use App\Shared\Core\Models\Staff;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStaffLink extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['user_id', 'staff_id', 'effective_from', 'effective_until', 'linked_by_user_id'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_until' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function linkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_by_user_id');
    }
}
