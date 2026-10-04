<?php

namespace App\Shared\Platform\Presentation;

use DateTimeInterface;
use Illuminate\Support\Carbon;

final class AcademicBusinessTime
{
    public static function timezone(): string
    {
        if (! function_exists('app') || ! app()->bound('config')) {
            return 'Asia/Jakarta';
        }

        return (string) config('academic.business_timezone', 'Asia/Jakarta');
    }

    public static function at(DateTimeInterface $value): Carbon
    {
        return Carbon::instance($value)->setTimezone(self::timezone());
    }

    public static function date(DateTimeInterface $value): string
    {
        return self::at($value)->toDateString();
    }

    public static function time(DateTimeInterface $value): string
    {
        return self::at($value)->format('H:i');
    }

    public static function dateTime(DateTimeInterface $value, string $format = 'd M Y, H:i'): string
    {
        return self::at($value)->format($format);
    }
}
