# Change Manifest — CI-PHP-CONTRACT-R2R

Date: 2026-09-28
Task: `CI-PHP-CONTRACT-R2R-FOUNDATION-ROUTING-REPAIR`
Branch: `chore/ci-php-r2r-foundation-routing-repair`
Baseline: `ad950339adfd6083338c5cb65238471a3d9841a1`

## Scope

Repair the foundation repository-routing check only. The canonical queue gate
remains `SOC-MD-06`.

Changed files:

- `.github/workflows/application-foundation.yml`
- this R2R change manifest
- durable state/evidence files listed below

`NEXT_ACTION.md` already contains the exact queue-compatible marker:
`**Next task ID:** `SOC-MD-06``. The marker is validated against
`codex/TASK_QUEUE.md` by `scripts/check_project_structure.py`.

The R2 PHP 8.4, runtime guard, Composer `~8.4.1`, and lockfile changes are
preserved unchanged. No application/business source, database, migration,
provider, OpenAI, dependency, runtime, or deployment change is included.

## Repair

- Added `NEXT_ACTION.md` to both `push.paths` and `pull_request.paths` for the
  foundation workflow because foundation verification consumes that file.
- Preserved the PHP setup, approved runtime guard, Composer validation, locked
  install, and foundation verification steps exactly.

## Validation

- `SOC-MD-06` present in `codex/TASK_QUEUE.md`: PASS.
- `python3 scripts/check_project_structure.py`: PASS expected after repair.
- PHP/Composer/runtime diff guard: PASS; no R2 implementation lines changed.
- Exact repair-commit GitHub Actions result: FAIL, run `36355381623` on
  commit `659f6b3947e85c2f50cbc6dcfdc506aaf4b8705b`.
- PHP setup, approved range guard, Composer validation, and locked install:
  PASS.
- `Run foundation verification`: reached and failed in PHPUnit after the
  routing checker passed.
- New independent blocker: 489 tests failed, 3 passed, 17 warnings, and 53
  assertions. SQLite rejects `ALTER TABLE students ALTER COLUMN student_code
  DROP NOT NULL` in
  `database/migrations/2026_09_05_000003_make_student_code_optional_and_add_identifier_columns.php:16`.

## Safety

- No database write, migration, seed, import, provider mutation, or external
  OpenAI request was performed.
- Rollback is a normal Git revert of the repair commit.

## Closeout rule

The routing blocker and PHP/lockfile blocker are substantively resolved by the
run evidence, but R2R is `PASS_WITH_NEW_BLOCKER`, not fully PASS. Do not route
to `ACADEMIC_WEB_COMPLETION_REVIEW` until the new SQLite compatibility blocker
is separately resolved and verified. Preserve `SOC-MD-06`.

If a new independent failure appears, record it exactly and stop without
claiming CI PASS.
