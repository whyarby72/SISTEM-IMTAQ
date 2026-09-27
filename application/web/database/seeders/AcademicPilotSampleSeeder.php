<?php

namespace Database\Seeders;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ClassSessionGenerator;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\StaffOrganizationalAssignment;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicPilotSampleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $from = '2026-07-01';
            $pilotFrom = '2026-09-04';
            $pilotUntil = '2026-09-13';

            $unit = OrganizationalUnit::firstOrCreate(
                ['unit_code' => 'PILOT-SCHOOL'],
                ['unit_name' => 'Sample School Pilot', 'unit_type' => 'SCHOOL', 'effective_from' => $from]
            );
            $location = $unit->locations()->firstOrCreate(
                ['location_code' => 'PILOT-ROOM-01'],
                ['location_name' => 'Ruang Sample Pilot', 'location_type' => 'CLASSROOM', 'effective_from' => $from]
            );
            $year = AcademicYear::firstOrCreate(
                ['year_code' => '2026/2027-PILOT'],
                ['display_name' => 'Tahun Ajaran 2026/2027 (Pilot)', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']
            );
            $semester = Semester::firstOrCreate(
                ['academic_year_id' => $year->id, 'semester_code' => 'S1-PILOT'],
                ['display_name' => 'Semester 1 Pilot', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']
            );
            $grade = GradeLevel::firstOrCreate(
                ['organizational_unit_id' => $unit->id, 'level_code' => 'TINGKAT-3-PILOT'],
                ['display_name' => 'Tingkat 3 Sample', 'sequence_no' => 3]
            );
            $subject = Subject::firstOrCreate(
                ['subject_code' => 'PILOT-TAHFIZH'],
                ['subject_name' => 'Tahfizh Sample Pilot']
            );

            $azhar = Staff::firstOrCreate(['staff_code' => 'PILOT-AZHAR'], ['full_name' => 'Azhar']);
            $waka = Staff::firstOrCreate(['staff_code' => 'PILOT-WAWAN-SN'], ['full_name' => 'Wawan SN']);
            $teacherA = Staff::firstOrCreate(['staff_code' => 'PILOT-GURU-3A'], ['full_name' => 'Guru Sample 3A']);
            $teacherB = Staff::firstOrCreate(['staff_code' => 'PILOT-GURU-3B'], ['full_name' => 'Guru Sample 3B']);
            foreach ([$azhar, $waka, $teacherA, $teacherB] as $staff) {
                StaffOrganizationalAssignment::firstOrCreate(
                    ['staff_id' => $staff->id, 'organizational_unit_id' => $unit->id, 'effective_from' => $from],
                    ['assignment_type' => 'STAFF', 'is_primary' => true, 'source_reference' => 'SAMPLE-PILOT']
                );
            }

            $azharUser = User::updateOrCreate(
                ['email' => 'azhar.pilot@example.test'],
                ['name' => 'Azhar (Pilot)', 'password' => 'password']
            );
            $wakaUser = User::updateOrCreate(
                ['email' => 'wawan.sn.pilot@example.test'],
                ['name' => 'Wawan SN (Pilot)', 'password' => 'password']
            );
            if (Schema::hasTable('user_staff_links')) {
                UserStaffLink::firstOrCreate(['user_id' => $azharUser->id], ['staff_id' => $azhar->id, 'effective_from' => $from]);
                UserStaffLink::firstOrCreate(['user_id' => $wakaUser->id], ['staff_id' => $waka->id, 'effective_from' => $from]);
            }
            foreach ([['WALI_KELAS', 'Wali Kelas', $azharUser], ['WAKA_AKADEMIK', 'Waka Akademik', $wakaUser]] as [$code, $name, $user]) {
                $role = Role::firstOrCreate(['code' => $code], ['name' => $name, 'is_system' => true]);
                UserRoleAssignment::firstOrCreate(
                    ['user_id' => $user->id, 'role_id' => $role->id, 'scope_type' => 'INSTITUTION'],
                    ['effective_from' => $from, 'assignment_reason' => 'SAMPLE-PILOT']
                );
            }

            foreach ([['3A', $teacherA], ['3B', $teacherB]] as [$section, $teacher]) {
                $class = AcademicClass::query()->whereIn('class_code', [$section, 'PILOT-'.$section])->first();
                if ($class === null) {
                    $class = AcademicClass::create([
                        'class_code' => $section,
                        'academic_year_id' => $year->id,
                        'organizational_unit_id' => $unit->id,
                        'grade_level_id' => $grade->id,
                        'section_code' => $section,
                        'display_name' => 'Kelas '.$section.' (Pilot)',
                    ]);
                } elseif ($class->class_code === 'PILOT-'.$section) {
                    $class->class_code = $section;
                    $class->save();
                }
                $homeroom = ClassHomeroomAssignment::query()
                    ->where('class_id', $class->id)
                    ->whereDate('effective_from', $from)
                    ->first();
                if ($homeroom === null) {
                    ClassHomeroomAssignment::create([
                        'class_id' => $class->id,
                        'staff_id' => $azhar->id,
                        'effective_from' => $from,
                        'assigned_at' => now(),
                        'reason' => 'SAMPLE-PILOT',
                    ]);
                }
                for ($index = 1; $index <= 5; $index++) {
                    $student = Student::firstOrCreate(
                        ['student_code' => 'PILOT-'.$section.'-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT)],
                        ['full_name' => 'Siswa Sample '.$section.' '.$index, 'entry_year' => 2026]
                    );
                    $enrollment = StudentClassEnrollment::query()
                        ->where('student_id', $student->id)
                        ->where('class_id', $class->id)
                        ->whereDate('effective_from', $from)
                        ->first();
                    if ($enrollment === null) {
                        StudentClassEnrollment::create([
                            'student_id' => $student->id,
                            'class_id' => $class->id,
                            'effective_from' => $from,
                            'reason' => 'SAMPLE-PILOT',
                            'source_reference' => 'SAMPLE-PILOT',
                        ]);
                    }
                }
                $assignment = TeachingAssignment::firstOrCreate(
                    ['assignment_code' => 'PILOT-'.$section.'-TAHFIZH'],
                    ['semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => $from, 'workflow_status' => 'APPROVED', 'source_reference' => 'SAMPLE-PILOT']
                );
                $rule = ScheduleRule::firstOrCreate(
                    ['teaching_assignment_id' => $assignment->id, 'weekday' => 5, 'start_time' => $section === '3A' ? '08:00' : '09:00'],
                    ['end_time' => $section === '3A' ? '09:00' : '10:00', 'recurrence_type' => 'EVERY_WEEK', 'location_id' => $location->id, 'effective_from' => $pilotFrom, 'effective_until' => $pilotUntil, 'workflow_status' => 'APPROVED']
                );
                app(ClassSessionGenerator::class)->generate($rule, $pilotFrom, $pilotUntil);
            }
        });
    }
}
