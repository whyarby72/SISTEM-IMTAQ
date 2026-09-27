# CURRENT TASK CONTEXT

**Task:** `CI-PHP-CONTRACT-D1` PHP Runtime / Lockfile Compatibility Diagnosis  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `REPOSITORY-CENTERED CI CONTRACT DIAGNOSIS`  
**Task branch:** `chore/CI-PHP-CONTRACT-D1-diagnosis`  
**State-basis commit before D1:** `6d3493bc9c8a1d013a4df944a06da6bfb3969f44`

## Problem

The exact baseline GitHub Actions workflow fails before tests at Composer dependency installation.

Known evidence:

- GitHub Actions run `36301085559`
- CI PHP `8.3.35`
- `application/web/composer.json` PHP constraint `^8.3`
- `application/web/composer.lock.platform.php` `^8.3`
- locked Symfony 8.1 packages require PHP `>=8.4.1`
- workflow `.github/workflows/application-foundation.yml` pins PHP `8.3`

The unresolved blocker is:

`CI_PHP_LOCKFILE_COMPATIBILITY`

D1 is diagnosis only. It must determine whether the supported outcome is:

- runtime target moved to PHP >=8.4.1;
- PHP 8.3 compatibility remains intentional and lockfile is incompatible;
- or runtime authority is still insufficient to choose.

## REQUIRED NOW

1. `codex/TASK_CONTEXTS/CI-PHP-CONTRACT-D1.md`
2. `PROJECT_STATE.json`
3. `TEST_MATRIX.csv`
4. `EVIDENCE_INDEX.json`
5. `.github/workflows/application-foundation.yml`
6. `application/web/composer.json`
7. `application/web/composer.lock`
8. `docs/07_implementation/DEPLOYMENT_STAGING_AND_ROLLBACK.md`
9. `NEXT_ACTION.md`
10. `AGENTS.md`

Use targeted reads/searches only. Do not bulk-read the repository.

## Expected writes

Diagnosis/evidence only:

- `codex/DIAGNOSTICS/CI-PHP-CONTRACT-D1-2026-09-27.md`
- repository-state/evidence artifacts only if factual state changes:
  - `PROJECT_STATE.json`
  - `TEST_MATRIX.csv`
  - `EVIDENCE_INDEX.json`
  - `codex/CURRENT_TASK_CONTEXT.md`
  - `NEXT_ACTION.md`

## Forbidden

No changes to:

- `application/web/composer.json`
- `application/web/composer.lock`
- `.github/workflows/application-foundation.yml`
- application/business source
- PHP/Composer installation
- dependencies/vendor
- migrations/database
- OpenAI/provider state
- deployment

Do not run `composer update`.

## Exit

Produce a read-only diagnosis, one explicit decision gate, and one narrow recommended next task.

Commit and push diagnostic/state evidence to this D1 branch, then stop for ChatGPT audit.
