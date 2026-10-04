# Change Manifest — Academic Wali Dashboard Maturity R2C

Date: 2026-10-05
Project: SISTEM-IMTAQ
Task: `ACADEMIC-WALI-DASHBOARD-MATURITY-R2C-PRE-UAT-USABILITY-HARDENING`
Branch: `feat/super-admin-user-access-preferences`

## Baseline

- Required starting HEAD: `5a3fe6ebdaf84c8879e527c31a919995bebdf414`
- C2 tested executable: `91f6716aa39a51251dfa0755d7735eb04b8c15ba`
- C2 exact CI: `37230836045` = SUCCESS
- C2 authoritative result: 15 passed, 573 warnings, 2440 assertions, 0 failed
- PILOT access/write: `NONE / NONE`
- Public Academic AI: `OFF`
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`

## Scope

Source changes are limited to the attendance Blade usability surface and
source-oriented regression tests, plus the required C1 governance correction.

- Bulk Present targets only `[data-attendance-select]:not(:disabled)` controls
  whose current value is blank.
- Existing nonblank statuses are not overwritten.
- Bulk action does not submit or finalize.
- Save Draft/Finalize submit events have a client-side in-flight guard,
  `aria-disabled=true`, and processing labels. Controls remain enabled until
  the submit event so required values are serialized normally.
- Finalized attendance feedback links back to the Academic dashboard and does
  not imply that a pending joint partner is complete.
- Existing stale recovery message and no-auto-retry behavior remain intact.

No application service semantics, authorization boundary, schema, migration,
dependency, runtime configuration, or PILOT data were changed.

## Verification

Local checks passed:

- `composer validate --strict`
- PHP lint for changed PHP files
- Pint `--test`
- `php artisan view:cache`
- `python3 scripts/check_project_structure.py`
- `git diff --check`

Focused PHPUnit is delegated to disposable PostgreSQL CI because the local
database identity guard rejects the protected PILOT target before any business
query or write.

## Known side effect disposition

`StudentAttendanceController::show()` retains the pre-existing bounded lazy
materialization of participant snapshots and primary teacher participation.
R2C adds no new GET-side business mutation. This remains a known P2 item and
is accepted only as a controlled-UAT observation boundary.

## Rollback

Revert the R2C implementation commit(s). No database rollback is required.

## Decision

`WALI_ATTENDANCE_READY_FOR_CONTROLLED_HUMAN_UAT`

Exact R2C CI `37235409651` on implementation HEAD
`aecded94649f400e558cb1bde9c5082d8784165a` passed with 15 tests, 575
warnings, 2452 assertions, and 0 failures. Human UAT remains a separate
supervised action and was not run automatically.
