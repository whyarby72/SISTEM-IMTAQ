<?php

namespace App\Shared\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGuardianRelationship extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id', 'guardian_id', 'relationship_type', 'effective_from', 'effective_until',
        'relationship_status', 'communication_priority', 'authorized_for_parent_reports',
        'source_reference', 'notes', 'version_no',
    ];

    protected $attributes = [
        'relationship_status' => 'ACTIVE',
        'authorized_for_parent_reports' => false,
        'version_no' => 1,
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'communication_priority' => 'integer',
            'authorized_for_parent_reports' => 'boolean',
            'version_no' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }
}
