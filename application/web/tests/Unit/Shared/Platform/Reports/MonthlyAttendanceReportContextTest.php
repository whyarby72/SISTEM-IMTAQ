<?php

namespace Tests\Unit\Shared\Platform\Reports;

use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Domains\Academic\Models\MonthlyStudentAttendanceSnapshot;
use App\Shared\Platform\Imports\Models\ImportBatch;
use App\Shared\Platform\Reports\MonthlyAttendanceReportContext;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class MonthlyAttendanceReportContextTest extends TestCase
{
    public function test_context_declares_legacy_snapshot_as_non_live_monthly_aggregate(): void
    {
        $batch = new ImportBatch(['batch_code' => 'IMTAQ-JUL-2026-MONTHLY', 'source_system' => 'IMTAQ_LEGACY']);
        $batch->id = 'batch-july-2026';
        $summary = new MonthlyAttendanceSummary(['period' => '2026-07', 'status' => 'PUBLISHED']);
        $summary->setRelation('importBatch', $batch);

        $context = (new MonthlyAttendanceReportContext())->resolve(new Collection([$summary]), new Collection());

        $this->assertSame('2026-07', $context['period']);
        $this->assertSame('LEGACY_MONTHLY_SNAPSHOT', $context['source_type']);
        $this->assertSame('IMTAQ_LEGACY', $context['source_system']);
        $this->assertSame('IMTAQ-JUL-2026-MONTHLY', $context['source_reference']);
        $this->assertFalse($context['is_live']);
        $this->assertTrue($context['is_historical_snapshot']);
        $this->assertSame('MONTHLY_AGGREGATE_SNAPSHOT', $context['data_grain']);
        $this->assertSame('Status arsip: Sudah disahkan', $context['publication_label']);
    }

    public function test_context_fails_closed_on_conflicting_snapshot_provenance(): void
    {
        $firstBatch = new ImportBatch(['batch_code' => 'LEGACY-A', 'source_system' => 'IMTAQ_LEGACY']);
        $firstBatch->id = 'batch-a';
        $secondBatch = new ImportBatch(['batch_code' => 'LEGACY-B', 'source_system' => 'OTHER_SOURCE']);
        $secondBatch->id = 'batch-b';
        $summary = new MonthlyAttendanceSummary(['period' => '2026-07', 'status' => 'DRAFT']);
        $summary->setRelation('importBatch', $firstBatch);
        $snapshot = new MonthlyStudentAttendanceSnapshot(['period' => '2026-07']);
        $snapshot->setRelation('importBatch', $secondBatch);

        $context = (new MonthlyAttendanceReportContext())->resolve(new Collection([$summary]), new Collection([$snapshot]));

        $this->assertTrue($context['provenance_conflict']);
        $this->assertNull($context['source_reference']);
        $this->assertSame('Referensi sumber tidak tersedia', $context['source_reference_label']);
        $this->assertSame('Status arsip: Menunggu persetujuan', $context['publication_label']);
    }
}
