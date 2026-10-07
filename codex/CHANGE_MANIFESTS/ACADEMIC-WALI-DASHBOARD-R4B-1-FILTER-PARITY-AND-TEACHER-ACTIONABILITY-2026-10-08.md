# Change Manifest — Academic Wali Dashboard R4B.1

## Identity

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WALI-DASHBOARD-R4B-1-FILTER-PARITY-AND-TEACHER-ACTIONABILITY`
- Branch: `feat/super-admin-user-access-preferences`
- Required starting HEAD: `0e2b52b5feb5b65bb6584b0af8ff751884e8802e`
- Entry branch/remote: verified identical
- Date: `2026-10-08` Asia/Jakarta

## Intended scope

- Use `period_state`/`needs_action` for Blade filters and state-owned summary
  counts.
- Gate teacher-attendance missing on the actual attendance obligation.
- Add legacy future-filter, complete filter-parity, occurrence-pending/HELD,
  future, and regression tests.

## Expected files

- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- this task context and manifest
- final state/evidence routing files only at closeout

## Safety

- PILOT access/write: `NONE / NONE`.
- Database/business data mutation: `NONE`.
- Migration/schema/dependency/runtime config: `NONE`.
- Public Academic AI: `OFF`.
- Human UAT: `DEFERRED`.
- R4C/replacement-teacher governance/Grade G3: not started.
- Pre-existing untracked `codex/AUDITS/` preserved and untouched.

## Validation at creation

- PHP lint: PASS.
- Pint changed-file check: PASS.
- Blade view cache: PASS.
- Route list: PASS.
- Project structure guard: PASS.
- `git diff --check`: PASS.
- Focused local tests: `BLOCKED_SAFE`; the database identity guard rejected
  the protected local PILOT identity before any business query/write.
- Exact disposable PostgreSQL CI: PENDING.

## Required closeout evidence

Record starting HEAD, tested executable HEAD, final governance HEAD, exact CI,
test/warning/assertion/failure totals, all six filter parity gates, legacy
future exclusion, canonical pending/HELD/future teacher actionability, R4B and
R4A regression, and the final decision.

## Recovery

Revert the R4B.1 application/test commit and evidence-only commits if exact CI
or audit fails. No database rollback is needed because no database was changed.

**Status:** `PENDING_EXACT_CI`
