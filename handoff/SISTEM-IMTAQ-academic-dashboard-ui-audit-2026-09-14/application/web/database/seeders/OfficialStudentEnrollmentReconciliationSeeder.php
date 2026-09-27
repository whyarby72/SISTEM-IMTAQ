<?php

namespace Database\Seeders;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Shared\Core\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficialStudentEnrollmentReconciliationSeeder extends Seeder
{
    public function run(): void
    {
        $pilotYear = AcademicYear::query()->where('year_code', '2026/2027-PILOT')->firstOrFail();
        $officialYear = AcademicYear::query()->where('year_code', '2026/2027')->firstOrFail();
        $officialClasses = AcademicClass::query()->where('academic_year_id', $officialYear->id)->get()->keyBy('class_code');

        DB::transaction(function () use ($pilotYear, $officialClasses): void {
            StudentClassEnrollment::query()
                ->where('status', 'ACTIVE')
                ->where('source_reference', 'like', 'IMTAQ-MASTER-2026-07:%')
                ->whereHas('academicClass', fn ($query) => $query->where('academic_year_id', $pilotYear->id))
                ->with('academicClass')
                ->orderBy('id')
                ->get()
                ->each(function (StudentClassEnrollment $enrollment) use ($officialClasses): void {
                    $pilotClassCode = $enrollment->academicClass->class_code;
                    $officialClass = $officialClasses->get('IMTAQ-2026-'.$pilotClassCode);
                    abort_if($officialClass === null, 422, "Kelas resmi untuk {$pilotClassCode} tidak ditemukan.");

                    $enrollment->update([
                        'class_id' => $officialClass->id,
                        'reason' => 'RECONCILIATION-OFFICIAL-2026',
                        'version_no' => $enrollment->version_no + 1,
                    ]);
                });
        });
    }
}
