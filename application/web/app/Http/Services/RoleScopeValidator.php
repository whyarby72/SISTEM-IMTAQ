<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleScopeValidator
{
    public const TYPES = [
        'SELF', 'ASSIGNED_SESSION', 'ASSIGNED_CLASS', 'HOMEROOM_CLASS',
        'DEPARTMENT', 'UNIT', 'INSTITUTION',
    ];

    public function validate(array $payload): array
    {
        $type = $payload['scope_type'] ?? null;
        $key = $payload['scope_key'] ?? null;

        if (! in_array($type, self::TYPES, true)) {
            throw ValidationException::withMessages(['scope_type' => 'Scope type tidak didukung.']);
        }

        if (in_array($type, ['SELF', 'INSTITUTION'], true) && $key !== null && $key !== '') {
            throw ValidationException::withMessages(['scope_key' => 'Scope key harus kosong untuk scope ini.']);
        }

        if (in_array($type, ['ASSIGNED_SESSION'], true) && (! $key || ! DB::table('class_sessions')->where('id', $key)->exists())) {
            throw ValidationException::withMessages(['scope_key' => 'Session scope tidak ditemukan.']);
        }

        if (in_array($type, ['ASSIGNED_CLASS', 'HOMEROOM_CLASS'], true) && (! $key || ! DB::table('classes')->where('id', $key)->exists())) {
            throw ValidationException::withMessages(['scope_key' => 'Class scope tidak ditemukan.']);
        }

        if (in_array($type, ['DEPARTMENT', 'UNIT'], true) && (! $key || ! DB::table('organizational_units')->where('id', $key)->exists())) {
            throw ValidationException::withMessages(['scope_key' => 'Unit scope tidak ditemukan.']);
        }

        if (! empty($payload['effective_from']) && ! empty($payload['effective_until']) && $payload['effective_from'] >= $payload['effective_until']) {
            throw ValidationException::withMessages(['effective_until' => 'Tanggal akhir harus setelah tanggal mulai.']);
        }

        return [
            'scope_type' => $type,
            'scope_key' => $key ?: null,
            'effective_from' => $payload['effective_from'] ?: null,
            'effective_until' => $payload['effective_until'] ?: null,
        ];
    }
}
