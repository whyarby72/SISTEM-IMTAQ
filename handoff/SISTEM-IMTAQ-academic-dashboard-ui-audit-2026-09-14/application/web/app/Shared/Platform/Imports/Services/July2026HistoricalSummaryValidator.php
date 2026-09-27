<?php

namespace App\Shared\Platform\Imports\Services;

use InvalidArgumentException;
use JsonException;

class July2026HistoricalSummaryValidator
{
    private const EXPECTED_MAPPINGS = [
        '1' => ['attendance_group' => '1', 'matiq_report_class' => 'Kelas 1', 'roster' => 20],
        '2A' => ['attendance_group' => '2A', 'matiq_report_class' => 'Kelas 2', 'roster' => 19],
        '2B' => ['attendance_group' => '2B-3B', 'matiq_report_class' => 'Kelas 2', 'roster' => 10],
        '3A' => ['attendance_group' => '3A', 'matiq_report_class' => 'Kelas 3', 'roster' => 15],
        '3B' => ['attendance_group' => '2B-3B', 'matiq_report_class' => 'Kelas 3', 'roster' => 20],
    ];

    private const SCHEDULE_UNITS = ['1' => 3, '2A' => 20, '3A' => 20, '2B-3B' => 20];

    public function validateFile(string $path, ?string $expectedChecksum = null): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("Seed file is not readable: {$path}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new InvalidArgumentException("Seed file could not be read: {$path}");
        }

        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Seed file contains invalid JSON.', previous: $exception);
        }

        $result = $this->validate($payload);
        $checksum = hash('sha256', $contents);
        $result['source'] = [
            'path' => $path,
            'sha256_checksum' => $checksum,
            'expected_checksum_matches' => $expectedChecksum === null || hash_equals(strtolower($expectedChecksum), $checksum),
        ];

        if (! $result['source']['expected_checksum_matches']) {
            $result['blocking_errors'][] = 'SOURCE_CHECKSUM_MISMATCH';
            $result['valid'] = false;
        }

        return $result;
    }

    public function validate(array $payload): array
    {
        $errors = [];
        $warnings = [];
        $metadata = $payload['metadata'] ?? [];
        $rows = $payload['class_attendance_summary'] ?? [];
        $overall = $payload['overall'] ?? [];

        if (($metadata['domain'] ?? null) !== 'ACADEMIC_ATTENDANCE') {
            $errors[] = 'DOMAIN_MUST_BE_ACADEMIC_ATTENDANCE';
        }
        if (($metadata['period'] ?? null) !== '2026-07') {
            $errors[] = 'PERIOD_MUST_BE_2026_07';
        }
        if (($metadata['validation_status'] ?? null) !== 'NOT_VALIDATED_LOCKED') {
            $warnings[] = 'SOURCE_VALIDATION_STATUS_REQUIRES_REVIEW';
        }

        $actualClassNames = array_values(array_filter(array_map(
            fn ($row): ?string => is_array($row) ? ($row['class_admin'] ?? null) : null,
            $rows,
        )));
        $expectedClassNames = array_keys(self::EXPECTED_MAPPINGS);
        sort($actualClassNames, SORT_STRING);
        sort($expectedClassNames, SORT_STRING);
        if (count($actualClassNames) !== count($expectedClassNames) || array_diff($actualClassNames, $expectedClassNames) !== [] || array_diff($expectedClassNames, $actualClassNames) !== []) {
            $errors[] = 'CLASS_ROWS_MUST_MATCH_EXPECTED_JULY_SCOPE';
        }

        $totals = array_fill_keys(['roster', 'present', 'permission', 'sick', 'absent', 'eligible', 'non_eligible'], 0);
        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['class_admin'])) {
                $errors[] = 'CLASS_ROW_IS_INVALID';

                continue;
            }
            $class = $row['class_admin'];
            $expected = self::EXPECTED_MAPPINGS[$class] ?? null;
            if ($expected === null) {
                $errors[] = "UNKNOWN_CLASS_{$class}";

                continue;
            }
            foreach (['attendance_group', 'roster'] as $field) {
                $value = $expected[$field];
                if (($row[$field] ?? null) !== $value) {
                    $errors[] = "CLASS_{$class}_{$field}_MISMATCH";
                }
            }
            foreach (array_keys($totals) as $field) {
                if (! is_int($row[$field] ?? null) || $row[$field] < 0) {
                    $errors[] = "CLASS_{$class}_{$field}_MUST_BE_NON_NEGATIVE_INTEGER";
                } else {
                    $totals[$field] += $row[$field];
                }
            }
            if (($row['eligible'] ?? null) !== (($row['present'] ?? 0) + ($row['permission'] ?? 0) + ($row['sick'] ?? 0) + ($row['absent'] ?? 0))) {
                $errors[] = "CLASS_{$class}_ELIGIBILITY_FORMULA_MISMATCH";
            }
        }

        $mappingRows = $payload['class_system']['mappings'] ?? [];
        $mappingByClass = [];
        foreach ($mappingRows as $mapping) {
            if (is_array($mapping) && isset($mapping['class_admin'])) {
                $mappingByClass[$mapping['class_admin']] = $mapping;
            }
        }
        foreach (self::EXPECTED_MAPPINGS as $class => $expected) {
            foreach (['attendance_group', 'matiq_report_class', 'roster'] as $field) {
                if (($mappingByClass[$class][$field] ?? null) !== $expected[$field]) {
                    $errors[] = "MAPPING_{$class}_{$field}_MISMATCH";
                }
            }
        }

        foreach ($totals as $field => $value) {
            if (($overall[$field] ?? null) !== $value) {
                $errors[] = "OVERALL_{$field}_MISMATCH";
            }
        }

        $opportunities = 0;
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['class_admin'], self::EXPECTED_MAPPINGS[$row['class_admin']])) {
                $opportunities += self::SCHEDULE_UNITS[$row['attendance_group'] ?? ''] * ($row['roster'] ?? 0);
            }
        }
        if ($opportunities !== $totals['eligible'] + $totals['non_eligible']) {
            $errors[] = 'SCHEDULE_OPPORTUNITIES_DO_NOT_RECONCILE';
        }

        $group = collect($payload['attendance_group_summary'] ?? [])->firstWhere('attendance_group', '2B-3B');
        if (is_array($group)) {
            foreach (['present', 'permission', 'sick', 'absent', 'eligible', 'non_eligible'] as $field) {
                $expectedGroupTotal = array_sum(array_map(fn ($row) => ($row['attendance_group'] ?? null) === '2B-3B' ? ($row[$field] ?? 0) : 0, $rows));
                if (($group[$field] ?? null) !== $expectedGroupTotal) {
                    $errors[] = "GROUP_2B_3B_{$field}_MISMATCH";
                }
            }
        } else {
            $warnings[] = 'GROUP_2B_3B_SUMMARY_NOT_PRESENT';
        }

        return [
            'valid' => $errors === [],
            'blocking_errors' => array_values(array_unique($errors)),
            'warnings' => array_values(array_unique($warnings)),
            'source_granularity' => 'MONTHLY_SUMMARY',
            'row_count' => count($rows),
            'totals' => $totals,
            'scheduled_opportunities' => $opportunities,
            'canonical_rows_created' => 0,
            'requires_human_approval' => true,
            'source' => ['sha256_checksum' => null],
        ];
    }
}
