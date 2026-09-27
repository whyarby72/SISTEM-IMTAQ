# CURRENT TASK CONTEXT

**Task:** `CI-PHP-CONTRACT-R1R` Runtime Constraint Precision Correction  
**State:** `COMPLETED / PASS`
**Branch:** `chore/ci-php-r1r-constraint-correction`  
**State-basis:** `1b4b1caf16635d0331651db30abc7e730d49267d`

Owner-approved runtime authority remains:

- PHP family: `8.4.x`
- minimum: `8.4.1`
- scope: CI, staging target, production target

## Required correction

The R1 plan currently proposes Composer PHP `^8.4.1`.

Correct it to:

`~8.4.1`

so the declared support range remains within PHP 8.4.x while enforcing minimum 8.4.1.

Also reconcile `PROJECT_STATE.json` acceptance IDs from stale D2 IDs to R1R IDs.

R1R correction is complete: the proposed Composer constraint is `~8.4.1`,
which means `>=8.4.1` and `<8.5.0`. The next implementation task remains
`CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84`.

## Required now

1. `codex/TASK_CONTEXTS/CI-PHP-CONTRACT-R1R.md`
2. `codex/PLANS/CI-PHP-CONTRACT-R1-ALIGN-RUNTIME-UPWARD-DESIGN-2026-09-27.md`
3. `PROJECT_STATE.json`
4. `NEXT_ACTION.md`

## Boundaries

Design/state correction only. No CI workflow, Composer manifest/lockfile, application, dependency, runtime, database, provider, or deployment mutation.

## Exit

After PASS:
- next task = `CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84`
- commit/push this branch
- STOP for ChatGPT audit.
