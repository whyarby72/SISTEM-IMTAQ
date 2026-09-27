<?php

namespace Tests\Feature\Shared;

use App\Shared\Core\Models\Location;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreFoundationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_core_staff_and_organization_tables_have_the_required_shape(): void
    {
        foreach ([
            'organizational_units',
            'locations',
            'staff',
            'staff_organizational_assignments',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table));
            $this->assertTrue(Schema::hasColumn($table, 'id'));
            $this->assertTrue(Schema::hasColumn($table, 'version_no'));
        }

        $this->assertTrue(Schema::hasColumn('organizational_units', 'parent_unit_id'));
        $this->assertTrue(Schema::hasColumn('locations', 'organizational_unit_id'));
        $this->assertTrue(Schema::hasColumn('staff_organizational_assignments', 'effective_from'));
    }

    public function test_staff_can_be_assigned_to_an_organizational_unit_and_location_can_reference_it(): void
    {
        $unit = OrganizationalUnit::create([
            'unit_code' => 'UNIT-CORE',
            'unit_name' => 'Core Unit',
            'unit_type' => 'UNIT',
        ]);
        $staff = Staff::create([
            'staff_code' => 'STF-001',
            'full_name' => 'Test Staff',
        ]);
        $staff->organizationalAssignments()->create([
            'organizational_unit_id' => $unit->id,
            'assignment_type' => 'MEMBER',
            'effective_from' => '2026-01-01',
        ]);
        $location = Location::create([
            'location_code' => 'LOC-001',
            'location_name' => 'Core Room',
            'location_type' => 'ROOM',
            'organizational_unit_id' => $unit->id,
        ]);

        $this->assertSame($unit->id, $staff->organizationalAssignments()->first()->organizationalUnit->id);
        $this->assertSame($unit->id, $location->organizationalUnit->id);
    }
}
