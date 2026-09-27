# Change Manifest — CI-PHP-CONTRACT-R2

Date: 2026-09-28
Task: `CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84`
Branch: `chore/ci-php-r2-align-ci-php84`
Baseline: `c86df2102bd7f6f0fb898a0887ee69882f2eb4b2`

## Scope

This implementation aligns the foundation workflow and Composer platform
contract with the approved PHP 8.4.x runtime authority, minimum PHP 8.4.1.

Changed files:

- `.github/workflows/application-foundation.yml`
- `application/web/composer.json`
- `application/web/composer.lock`
- this change manifest

No application/business source, database, migration, provider, OpenAI,
deployment, hosting, Laravel-version, or unrelated workflow change was made.

## Implementation

- CI setup target changed from PHP `8.3` to `8.4`.
- CI now rejects runtimes below `8.4.1` or at/above `8.5.0`.
- Composer root PHP requirement changed from `^8.3` to `~8.4.1`.
- Lockfile was regenerated with `composer update --lock --no-install`.
- Lockfile package name/version set was compared before and after; all 119
  locked package name/version entries are unchanged.
- Lockfile changes are limited to Composer content hash and platform PHP
  metadata.

## Validation evidence

- `composer validate --strict`: PASS.
- Package-version drift guard: PASS; no package version changed.
- Full PHPUnit run through foundation verification: PASS, 509 tests and
  2,191 assertions.
- `composer check-platform-reqs --lock`: NOT PASS locally because the only
  available runtime is PHP `8.5.10`, outside the approved `<8.5.0` range.
- `composer install --no-interaction --prefer-dist --no-progress`: deferred
  to the PHP 8.4 CI runner; no approved PHP 8.4 runtime is installed locally.
- `./scripts/verify-foundation.sh`: reaches and passes Composer validation,
  config clear, route listing, and PHPUnit, then reports the existing
  repository-structure routing failure: `NEXT_ACTION has no task id`.
- Exact implementation-commit GitHub Actions result: pending push and CI run.

## Safety and rollback

- No database write, migration, seed, import, provider mutation, or external
  OpenAI request was performed.
- Rollback is a normal Git revert of the implementation commit; no database
  rollback is required.
- R2 remains `PASS_WITH_NEW_BLOCKER` unless exact-current GitHub Actions
  reaches foundation verification and passes all required checks.

## Required follow-up

Record the exact implementation commit and GitHub Actions run after push.
If CI reaches verification/tests and exposes an independent failure, record it
as a new blocker; do not mark the PHP/lockfile alignment fully PASS.
