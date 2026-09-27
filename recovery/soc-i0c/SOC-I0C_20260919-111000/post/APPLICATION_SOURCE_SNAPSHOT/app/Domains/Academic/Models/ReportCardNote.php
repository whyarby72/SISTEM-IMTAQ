<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['report_card_version_id', 'note_text', 'approved_for_parent_report', 'created_by', 'updated_by'];

    protected $attributes = ['approved_for_parent_report' => false];

    protected function casts(): array
    {
        return ['approved_for_parent_report' => 'boolean'];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ReportCardVersion::class, 'report_card_version_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
