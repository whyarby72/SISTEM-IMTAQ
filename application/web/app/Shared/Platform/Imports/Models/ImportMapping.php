<?php

namespace App\Shared\Platform\Imports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportMapping extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_mappings';

    protected $fillable = ['import_batch_id', 'source_type', 'source_key', 'target_type', 'target_id', 'mapping_status', 'confidence', 'reviewed_by_user_id', 'reviewed_at', 'evidence'];

    protected function casts(): array
    {
        return ['confidence' => 'decimal:4', 'reviewed_at' => 'datetime', 'evidence' => 'array'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
