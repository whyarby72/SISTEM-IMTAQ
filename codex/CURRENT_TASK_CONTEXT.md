# CURRENT TASK CONTEXT

**Task:** `CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `CI / RUNTIME ALIGNMENT IMPLEMENTATION`  
**Branch:** `chore/ci-php-r2-align-ci-php84`  
**State-basis before R2:** `e7d712aee6aac0d16e81c5de3660ee4d824f476d`

## Approved runtime authority

- PHP family: `8.4.x`
- minimum: `8.4.1`
- Composer root target: `~8.4.1`
- scope: CI, staging target, production target

## Problem

Current workflow still uses PHP `8.3`, while the lockfile contains packages requiring PHP `>=8.4.1`. GitHub Actions therefore fails at locked dependency installation before tests.

## REQUIRED NOW

1. `codex/TASK_CONTEXTS/CI-PHP-CONTRACT-R2.md`
2. `codex/PLANS/CI-PHP-CONTRACT-R1-ALIGN-RUNTIME-UPWARD-DESIGN-2026-09-27.md`
3. `codex/DECISIONS/CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-2026-09-27.md`
4. `.github/workflows/application-foundation.yml`
5. `application/web/composer.json`
6. `application/web/composer.lock`
7. `PROJECT_STATE.json`
8. `NEXT_ACTION.md`
9. `AGENTS.md`

## Authorized implementation

- CI PHP `8.3 -> 8.4`
- executable PHP range guard: `>=8.4.1 && <8.5.0`
- composer root PHP `^8.3 -> ~8.4.1`
- controlled lockfile regeneration with package-version drift guard
- repository verification and exact-current GitHub Actions verification
- evidence/state closeout

## Critical guard

No dependency version upgrade/downgrade is authorized by default.

If package name/version set changes unexpectedly during lock regeneration:
`STOP = R2_HOLD_UNEXPECTED_DEPENDENCY_DRIFT`

## Forbidden

No application/business source, database/migration, provider/OpenAI, deployment, hosting, Laravel-version, or main-merge changes.

## Exit

Push implementation/evidence. Stop for ChatGPT audit.

If exact-current CI passes, the PHP/lockfile blocker may be marked resolved and the next track returns to Academic web completion/review.
