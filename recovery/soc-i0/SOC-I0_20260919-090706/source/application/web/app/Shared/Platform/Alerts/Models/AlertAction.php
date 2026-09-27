<?php

namespace App\Shared\Platform\Alerts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertAction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['alert_id', 'actor_user_id', 'action_type', 'from_status', 'to_status', 'notes'];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
