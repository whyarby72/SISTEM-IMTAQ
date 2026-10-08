<?php

namespace Tests\Feature\Admin;

use App\Shared\Platform\Reports\MonthlyAttendanceReportExportService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MonthlyStudentAttendanceExportTest extends TestCase
{
    public function test_student_detail_exports_include_historical_snapshot_fields(): void
    {
        $class = (object) ['display_name' => 'Kelas 3A'];
        $students = new Collection([(object) [
            'name_indonesia' => 'Santri Uji',
            'name_arabic' => 'طالب اختبار',
            'source_record_id' => 'JUL26-001',
            'present' => 20,
            'permission' => 1,
            'sick' => 0,
            'absent' => 1,
            'attendance_rate' => 0.909091,
        ]]);

        $exporter = app(MonthlyAttendanceReportExportService::class);
        $csv = $exporter->studentCsv($class, $students);
        $pdf = $exporter->studentPdf($class, $students);

        $this->assertStringContainsString('Kelas 3A', $csv);
        $this->assertStringContainsString('JUL26-001', $csv);
        $this->assertStringContainsString('90,91%', $csv);
        $this->assertStringContainsString('LEGACY_MONTHLY_SNAPSHOT', $csv);
        $this->assertStringContainsString('Historical attendance snapshot', $csv);
        $this->assertStringContainsString('"Live data","NO"', $csv);
        $this->assertStringNotContainsString('Jumlah Sesi', $csv);
        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertGreaterThan(10000, strlen($pdf));
        $this->assertStringContainsString('/Type0', $pdf);
    }
}
