<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassLineageMapping extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'source_class_reference', 'target_class_id', 'mapping_context', 'effective_from',
        'effective_until', 'status', 'approved_by', 'approved_at', 'approval_reason',
    ];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_until' => 'date', 'approved_at' => 'datetime'];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'target_class_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
