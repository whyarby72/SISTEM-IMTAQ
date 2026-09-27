# Migration B Infrastructure-Only Safety Review

Migration: `2026_09_17_000001_create_attendance_semantic_foundation_tables.php`

## Decision

`SEMANTIC_FOUNDATION_MIGRATION_DISPOSITION = APPLY_BEFORE_SOC`

`MIGRATION_B_DEPLOYMENT_MODE = INFRASTRUCTURE_ONLY`

The migration creates `attendance_source_certifications`, `class_lineage_mappings`, and two nullable participant columns. It contains no data update/backfill, no seed, and no automatic population.

## Application-reference audit

Current source references are explicit services/models/tests: `AttendanceSourceCertificationService`, `AttendanceSourceAuthorityResolver`, `ClassLineageResolver`, `CanonicalAttendanceSemanticService`, and `SessionStudentParticipant`. No observer, listener, job, scheduled command, middleware, boot hook, or route was found that automatically populates these structures merely because the tables exist. Empty authority tables therefore do not become authoritative; explicit certification and approved mapping rows are required.

Creating the tables changes previously unavailable explicit service calls from missing-table errors to callable infrastructure. It does not automatically activate source precedence, certification, class lineage, NON_ELIGIBLE, historical reclassification, or backfill. This is a controlled infrastructure effect, not automatic semantic activation.

## Eligibility effect

`eligibility_status` and `non_eligible_reason` are nullable with no default and no CHECK in Migration B. Existing participant rows receive NULL; new rows also receive NULL unless the caller supplies a value. Existing canonical code already treats NULL as `ELIGIBLE` when `is_required=true` and `NON_ELIGIBLE` when false, so applying the columns alone does not rewrite rows or alter the existing fallback. No participant is reclassified by this task.

## Frozen prohibitions

- backfill: NO
- eligibility reclassification: NO
- NON_ELIGIBLE activation: NO; MD-02 authority OPEN
- source-authority precedence activation/population: NO
- class-lineage population/backfill: NO
- historical attendance or occurrence rewrite: NO

`MIGRATION_B_TECHNICAL_PREFLIGHT = PASS` and `MIGRATION_B_INFRASTRUCTURE_ONLY_SAFE = YES`, subject to the restore gate and a separate atomic execution authorization. Migration was not executed.
