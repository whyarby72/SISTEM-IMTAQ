<?php

namespace App\Shared\Platform\Imports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_batches';

    protected $fillable = ['batch_code', 'source_system', 'source_period', 'status', 'created_by_user_id', 'received_at', 'source_total', 'imported_count', 'rejected_count', 'quarantined_count', 'duplicate_count', 'excluded_count'];

    protected function casts(): array
    {
        return ['received_at' => 'datetime', 'source_total' => 'integer', 'imported_count' => 'integer', 'rejected_count' => 'integer', 'quarantined_count' => 'integer', 'duplicate_count' => 'integer', 'excluded_count' => 'integer'];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ImportFile::class, 'import_batch_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'import_batch_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(ImportMapping::class, 'import_batch_id');
    }

    public function lineages(): HasMany
    {
        return $this->hasMany(ImportLineage::class, 'import_batch_id');
    }
}
