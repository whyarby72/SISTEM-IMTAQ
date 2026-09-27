<?php

namespace App\Domains\Academic\AI\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

final class AcademicAiInput
{
    public static function validated(array $input, array $rules): array
    {
        $unknown = array_diff(array_keys($input), array_keys($rules));
        if ($unknown !== []) {
            abort(422, 'Field AI tidak dikenal: '.implode(', ', $unknown));
        }

        $validated = Validator::make($input, $rules)->validate();

        foreach (['period_start', 'period_end'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = Carbon::parse($validated[$field]);
            }
        }

        if (isset($validated['period_start'], $validated['period_end'])) {
            if ($validated['period_start']->greaterThan($validated['period_end'])) {
                abort(422, 'period_start harus sama atau sebelum period_end.');
            }
            if ($validated['period_start']->diffInDays($validated['period_end']) > 366) {
                abort(422, 'Rentang periode maksimal 366 hari.');
            }
        }

        return $validated;
    }
}
