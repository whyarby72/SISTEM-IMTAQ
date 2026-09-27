<?php

namespace App\Shared\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentIdentifier extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id', 'identifier_type', 'identifier_value', 'identifier_scope',
        'verification_status', 'record_status', 'valid_from', 'valid_until',
        'source_reference', 'version_no',
    ];

    protected $attributes = [
        'verification_status' => 'UNVERIFIED',
        'record_status' => 'ACTIVE',
        'version_no' => 1,
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'version_no' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
