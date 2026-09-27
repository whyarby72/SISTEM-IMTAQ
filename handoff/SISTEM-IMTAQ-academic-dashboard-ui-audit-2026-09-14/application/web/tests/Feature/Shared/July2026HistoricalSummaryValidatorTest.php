<?php

namespace Tests\Feature\Shared;

use App\Shared\Platform\Imports\Services\July2026HistoricalSummaryValidator;
use Tests\TestCase;

class July2026HistoricalSummaryValidatorTest extends TestCase
{
    public function test_july_seed_reconciles_without_creating_canonical_rows(): void
    {
        $result = app(July2026HistoricalSummaryValidator::class)->validate($this->loadSeed());

        $this->assertTrue($result['valid']);
        $this->assertSame([], $result['blocking_errors']);
        $this->assertSame(5, $result['row_count']);
        $this->assertSame(1340, $result['scheduled_opportunities']);
        $this->assertSame(1198, $result['totals']['eligible']);
        $this->assertSame(142, $result['totals']['non_eligible']);
        $this->assertSame(0, $result['canonical_rows_created']);
    }

    public function test_validator_rejects_eligibility_formula_drift(): void
    {
        $seed = $this->loadSeed();
        $seed['class_attendance_summary'][0]['eligible'] = 59;

        $result = app(July2026HistoricalSummaryValidator::class)->validate($seed);

        $this->assertFalse($result['valid']);
        $this->assertContains('CLASS_1_ELIGIBILITY_FORMULA_MISMATCH', $result['blocking_errors']);
    }

    private function loadSeed(): array
    {
        return json_decode(file_get_contents('/Users/afradadmedia/Downloads/IMTAQ_ATTENDANCE_JULY_2026_SEED.json'), true, 512, JSON_THROW_ON_ERROR);
    }
}
