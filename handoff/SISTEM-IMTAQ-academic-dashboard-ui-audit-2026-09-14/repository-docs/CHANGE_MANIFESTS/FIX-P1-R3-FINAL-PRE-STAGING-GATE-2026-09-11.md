# Change Manifest

- Change ID: FIX-P1-R3-FINAL-PRE-STAGING-GATE-2026-09-11
- Status: READY_FOR_STAGING — staging execution not performed
- Git metadata: unavailable in repository workspace

## Final changeset audit

Post-P0 changes are explained by the P1 manifests: policy/docs, Academic authorization, RBAC bootstrap safety and retired-role documentation, teacher attendance, substitution/cross-path locking, correction workflow, PostgreSQL CHECK migration source, tests, and staging artifacts. No unexplained runtime source change was found. No UI/P2 work was started.

## Active authority

- `WALI_KELAS`: effective homeroom scope.
- `WAKA_AKADEMIK`: full Academic authority via `academic.domain.manage`.
- `SUPER_ADMIN`: full institution authority via `platform.institution.manage`.
- `ADMIN_AKADEMIK`: retired; no active runtime authorization reference; no auto-promotion.
- Real active `ADMIN_AKADEMIK` assignment inventory: `NOT_INSPECTED` because no real database was accessed.

## Lock and correction gates

Canonical teacher lock is used by substitution, swap, reschedule, extra/ad-hoc creation, session generation, and primary participation materialization. Key derivation is PostgreSQL `hashtextextended('academic-teacher-obligation:' || teacher_id, 0)` with `pg_advisory_xact_lock(bigint)`; multi-teacher keys are sorted deterministically. Correction review/apply locks and version checks remain intact.

## PostgreSQL CHECK source

Migration `2026_09_11_000020_add_academic_controlled_vocabulary_checks.php` declares exactly nine named checks, preserves lawful NULL attendance values, performs no data rewrite, and `down()` drops only those nine constraints. Actual PostgreSQL audit/application/proof are not run.

## Local verification

- Targeted P0/P1/R1/R2 writers and lock tests: **27 tests / 92 assertions / 0 failures**.
- Academic regression: **213 tests / 817 assertions / 0 failures**.
- Shared/Auth regression: **63 tests / 273 assertions / 0 failures**.
- Admin regression: **44 tests / 164 assertions / 0 failures**.
- View cache: PASS.
- Pint/lint: PASS.

## Read-only staging artifacts

- SQL: `codex/STAGING/P1_POSTGRES_PREFLIGHT_READONLY.sql`
- Runbook: `codex/STAGING/P1_PHASE_7_STAGING_HANDOFF.md`
- Bundle README: `codex/STAGING/P1-R3-CHECKPOINT-README.md`

## Explicit staging gates

1. PostgreSQL version/schema/migration identity and distinct vocabulary audit.
2. Active `ADMIN_AKADEMIK` assignment review; unresolved assignments abort authority bootstrap/sign-off.
3. Non-destructive authority bootstrap approval/execution.
4. CHECK migration and valid/invalid/NULL proof.
5. Real PostgreSQL teacher, substitution, cross-path, and correction contention tests.
6. Backup/restore, monitoring, rollback, audit-log inspection, and sign-off.

No gate above was executed in R3.
