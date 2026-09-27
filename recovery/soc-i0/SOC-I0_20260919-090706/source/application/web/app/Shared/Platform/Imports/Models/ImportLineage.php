<?php

namespace App\Shared\Platform\Imports\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLineage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_lineages';

    protected $fillable = ['import_batch_id', 'import_file_id', 'import_row_id', 'target_type', 'target_id', 'relationship_type'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class, 'import_file_id');
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(ImportRow::class, 'import_row_id');
    }
}
