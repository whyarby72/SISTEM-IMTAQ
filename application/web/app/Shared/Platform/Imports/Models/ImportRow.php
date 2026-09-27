<?php

namespace App\Shared\Platform\Imports\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportRow extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_rows';

    protected $fillable = ['import_batch_id', 'import_file_id', 'row_number', 'source_key', 'raw_payload', 'row_status', 'canonical_entity_type', 'canonical_entity_id', 'accounted_at'];

    protected function casts(): array
    {
        return ['raw_payload' => 'array', 'row_number' => 'integer', 'accounted_at' => 'datetime'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class, 'import_file_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportRowError::class, 'import_row_id');
    }

    public function lineages(): HasMany
    {
        return $this->hasMany(ImportLineage::class, 'import_row_id');
    }
}
