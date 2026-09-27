# CURRENT TASK CONTEXT

**Task:** `CI-PHP-CONTRACT-R2R-FOUNDATION-ROUTING-REPAIR`  
**State:** `PASS_WITH_NEW_BLOCKER`
**Current phase:** `CI / FOUNDATION ROUTING REPAIR`  
**Branch:** `chore/ci-php-r2r-foundation-routing-repair`  
**State-basis before R2R:** `523ddc3030c539eb98d0a0388affac7aa060758f`

## Verified R2 outcome

Exact R2 implementation commit:
`e4387589a60c1e6609536d22b262bc7ba0ffbdaf`

Exact GitHub Actions run:
`36353770932`

Verified steps:
- PHP setup: PASS
- approved PHP range assertion: PASS
- Composer validation: PASS
- locked dependency install: PASS
- foundation verification: FAIL

Therefore `CI_PHP_LOCKFILE_COMPATIBILITY` is no longer the active CI cause.

Active blocker:
`FOUNDATION_ROUTING_NEXT_ACTION_ID`

## Required now

1. `codex/TASK_CONTEXTS/CI-PHP-CONTRACT-R2R-FOUNDATION-ROUTING-REPAIR.md`
2. `NEXT_ACTION.md`
3. `codex/TASK_QUEUE.md`
4. `scripts/check_project_structure.py`
5. `.github/workflows/application-foundation.yml`
6. `PROJECT_STATE.json`
7. `codex/CHANGE_MANIFESTS/CI-PHP-CONTRACT-R2-2026-09-27.md`

## Repair

- restore queue-compatible marker in `NEXT_ACTION.md`:
  `**Next task ID:** `SOC-MD-06``
- add `NEXT_ACTION.md` to foundation workflow path filters
- preserve all R2 PHP/Composer/runtime changes unchanged
- reconcile stale evidence that exact Actions was unverified

## Exit

Obtain exact repair-commit Actions evidence.

If CI passes:
- close routing blocker;
- close PHP/lockfile blocker;
- set next product track to Academic web completion/review;
- preserve canonical governance gate `SOC-MD-06`;
- STOP for ChatGPT audit.
