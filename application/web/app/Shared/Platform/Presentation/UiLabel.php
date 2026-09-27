<?php

namespace App\Shared\Platform\Presentation;

final class UiLabel
{
    public static function clean(mixed $value): string
    {
        $label = trim((string) $value);

        return trim((string) preg_replace('/\s*(?:\(Pilot\)|Sample Pilot|Sample|Pilot)$/iu', '', $label));
    }
}
