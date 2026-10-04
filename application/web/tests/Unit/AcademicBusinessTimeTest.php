<?php

namespace Tests\Unit;

use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class AcademicBusinessTimeTest extends TestCase
{
    public function test_utc_backed_session_is_presented_in_the_configured_business_timezone(): void
    {
        $start = Carbon::parse('2026-10-05 01:00:00', 'UTC');
        $end = Carbon::parse('2026-10-05 02:30:00', 'UTC');

        $businessStart = AcademicBusinessTime::at($start);
        $businessEnd = AcademicBusinessTime::at($end);

        $this->assertSame('Asia/Jakarta', AcademicBusinessTime::timezone());
        $this->assertSame('08:00', AcademicBusinessTime::time($start));
        $this->assertSame('09:30', AcademicBusinessTime::time($end));
        $this->assertSame('5 Oktober 2026', $businessStart->locale('id')->translatedFormat('j F Y'));
        $this->assertSame('2026-10-05', AcademicBusinessTime::date($start));
        $this->assertSame('UTC', $start->getTimezone()->getName());
        $this->assertSame('Asia/Jakarta', $businessStart->getTimezone()->getName());
    }

    public function test_utc_midnight_boundary_uses_the_business_date_without_mutating_storage_value(): void
    {
        $stored = Carbon::parse('2026-10-04 18:30:00', 'UTC');

        $this->assertSame('2026-10-05', AcademicBusinessTime::date($stored));
        $this->assertSame('01:30', AcademicBusinessTime::time($stored));
        $this->assertSame('2026-10-04 18:30:00', $stored->toDateTimeString());
        $this->assertSame('UTC', $stored->getTimezone()->getName());
    }
}
