<?php

namespace App\Shared\Platform\Imports\Services;

use App\Domains\Academic\Models\AcademicClass;

class July2026OfficialClassMappingService
{
    private const MAP = [
        '1' => 'IMTAQ-2026-1', '2A' => 'IMTAQ-2026-2A', '2B' => 'IMTAQ-2026-2B',
        '3A' => 'IMTAQ-2026-3A', '3B' => 'IMTAQ-2026-3B',
    ];

    public function sourceCodes(): array
    {
        return array_keys(self::MAP);
    }

    public function canonicalCode(string $sourceCode): ?string
    {
        return self::MAP[$sourceCode] ?? null;
    }

    /** @return array{valid: bool, classes: array<string, AcademicClass>, errors: array<string>} */
    public function resolve(): array
    {
        $classes = [];
        $errors = [];
        foreach (self::MAP as $sourceCode => $canonicalCode) {
            $candidates = AcademicClass::query()->with('academicYear')
                ->where('class_code', $canonicalCode)->where('status', 'ACTIVE')
                ->whereHas('academicYear', fn ($query) => $query->where('year_code', '2026/2027')->where('status', 'ACTIVE'))
                ->get();
            if ($candidates->count() !== 1) {
                $errors[] = $candidates->isEmpty() ? "MISSING_OFFICIAL_CLASS_{$sourceCode}" : "AMBIGUOUS_OFFICIAL_CLASS_{$sourceCode}";

                continue;
            }
            $classes[$sourceCode] = $candidates->first();
        }

        $ids = array_map(fn (AcademicClass $class): string => (string) $class->id, $classes);
        if (count($classes) !== count(self::MAP)) {
            $errors[] = 'TARGET_CLASS_SET_MUST_CONTAIN_EXACTLY_FIVE_OFFICIAL_CLASSES';
        }
        if (count($ids) !== count(array_unique($ids))) {
            $errors[] = 'TARGET_CLASS_SET_CONTAINS_DUPLICATE_IDS';
        }

        return ['valid' => $errors === [], 'classes' => $classes, 'errors' => array_values(array_unique($errors))];
    }

    public function resolveOrFail(): array
    {
        $result = $this->resolve();
        if (! $result['valid']) {
            throw new \InvalidArgumentException('Official July class mapping failed: '.implode(', ', $result['errors']));
        }

        return $result['classes'];
    }
}
