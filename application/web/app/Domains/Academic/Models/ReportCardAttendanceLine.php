<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ReportCardAttendanceLine extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['report_card_version_id', 'status_code', 'status_count'];

    protected function casts(): array
    {
        return ['status_count' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Report card attendance snapshots are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Report card attendance snapshots are immutable.'));
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ReportCardVersion::class, 'report_card_version_id');
    }
}
