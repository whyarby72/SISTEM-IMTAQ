<?php

namespace App\Domains\Academic\AI\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProviderConfiguration extends Model
{
    use HasUuids;

    protected $table = 'ai_provider_configurations';

    protected $fillable = ['provider', 'credential_id', 'model', 'max_output_tokens', 'status', 'verification_metadata', 'verified_at', 'activated_at'];

    protected $hidden = ['verification_metadata'];

    protected function casts(): array
    {
        return ['max_output_tokens' => 'integer', 'verification_metadata' => 'array', 'verified_at' => 'datetime', 'activated_at' => 'datetime'];
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(AiProviderCredential::class, 'credential_id');
    }
}
