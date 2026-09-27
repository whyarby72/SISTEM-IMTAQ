<?php

namespace App\Shared\Platform\Imports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRowError extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_row_errors';

    protected $fillable = ['import_row_id', 'severity', 'error_code', 'field_name', 'message', 'details', 'resolved_by_user_id', 'resolved_at'];

    protected function casts(): array
    {
        return ['details' => 'array', 'resolved_at' => 'datetime'];
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(ImportRow::class, 'import_row_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
