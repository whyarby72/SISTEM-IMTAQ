<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Staff;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ReportCardSignatory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['report_card_version_id', 'signatory_role', 'staff_id', 'name_snapshot', 'title_snapshot'];

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Report card signatories are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Report card signatories are immutable.'));
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ReportCardVersion::class, 'report_card_version_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
