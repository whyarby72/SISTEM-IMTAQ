<?php

namespace App\Domains\Academic\Services;

use Illuminate\Support\Facades\DB;

class TeacherObligationLockService
{
    public function lockTeachers(array $teacherIds): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->orderedTeacherIds($teacherIds) as $teacherId) {
            DB::select(
                'select pg_advisory_xact_lock(hashtextextended(?, 0))',
                [$this->lockName($teacherId)]
            );
        }
    }

    public function orderedTeacherIds(array $teacherIds): array
    {
        $ids = array_values(array_unique(array_map(static fn ($id): string => (string) $id, $teacherIds)));
        sort($ids, SORT_STRING);

        return $ids;
    }

    public function lockName(string $teacherId): string
    {
        return 'academic-teacher-obligation:'.$teacherId;
    }
}
