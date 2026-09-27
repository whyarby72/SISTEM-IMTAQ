<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Staff;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicTranscriptSignatory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['academic_transcript_version_id', 'signatory_role', 'staff_id', 'name_snapshot', 'title_snapshot'];

    public function version(): BelongsTo
    {
        return $this->belongsTo(AcademicTranscriptVersion::class, 'academic_transcript_version_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
