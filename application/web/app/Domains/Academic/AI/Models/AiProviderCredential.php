<?php

namespace App\Domains\Academic\AI\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProviderCredential extends Model
{
    use HasUuids;

    protected $table = 'ai_provider_credentials';

    protected $fillable = ['provider', 'label', 'encrypted_secret', 'secret_last4', 'status', 'verified_at', 'revoked_at'];

    protected $hidden = ['encrypted_secret'];

    protected function casts(): array
    {
        return ['encrypted_secret' => 'encrypted', 'verified_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(AiProviderConfiguration::class, 'credential_id');
    }
}
