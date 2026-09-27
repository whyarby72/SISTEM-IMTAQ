<?php

namespace App\Shared\Platform\Imports\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportFile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_files';

    protected $fillable = ['import_batch_id', 'original_filename', 'storage_path', 'sha256_checksum', 'source_granularity', 'file_size_bytes', 'received_at'];

    protected function casts(): array
    {
        return ['file_size_bytes' => 'integer', 'received_at' => 'datetime'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'import_file_id');
    }

    public function lineages(): HasMany
    {
        return $this->hasMany(ImportLineage::class, 'import_file_id');
    }
}
