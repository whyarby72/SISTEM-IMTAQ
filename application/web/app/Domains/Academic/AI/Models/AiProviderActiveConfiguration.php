<?php

namespace App\Domains\Academic\AI\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProviderActiveConfiguration extends Model
{
    use HasUuids;

    protected $table = 'ai_provider_active_configurations';

    protected $fillable = ['provider', 'configuration_id', 'runtime_enabled', 'version_no'];

    protected function casts(): array
    {
        return ['runtime_enabled' => 'boolean', 'version_no' => 'integer'];
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(AiProviderConfiguration::class, 'configuration_id');
    }
}
