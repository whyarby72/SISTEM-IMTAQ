# CURRENT TASK CONTEXT

**Task:** `CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-GATE`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `CI / RUNTIME AUTHORITY GOVERNANCE`  
**Task branch:** `chore/CI-PHP-CONTRACT-D2-runtime-authority-gate`  
**State-basis before D2:** `1c8450081d7178336fbe6238be67f3291b2f3cea`

## Problem

D1/D1R proved a real runtime/dependency conflict:

- CI PHP: `8.3`
- local development PHP observed by D1: `8.5.10`
- root Composer PHP: `^8.3`
- lock platform PHP: `^8.3`
- 17 locked Symfony 8.1 packages require PHP `>=8.4.1`
- staging/production runtime authority: unresolved
- blocker: `CI_PHP_LOCKFILE_COMPATIBILITY`

D2 must establish explicit runtime authority or return HOLD. It must not implement the fix.

## REQUIRED NOW

1. `codex/TASK_CONTEXTS/CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-GATE.md`
2. `codex/DIAGNOSTICS/CI-PHP-CONTRACT-D1-2026-09-27.md`
3. `PROJECT_STATE.json`
4. `NEXT_ACTION.md`
5. `docs/07_implementation/DEPLOYMENT_STAGING_AND_ROLLBACK.md`
6. `docs/00_governance/POLICY_PENDING_REGISTER.md`
7. `project_management/DECISION_LOG.md`
8. `project_management/TECHNICAL_DECISIONS.md`
9. `AGENTS.md`

Use targeted reads only.

## Decision options

- Option A: canonical target runtime becomes PHP >=8.4.1.
- Option B: PHP 8.3 compatibility remains an intentional requirement.
- Option C: HOLD until hosting/staging/production authority is established.

Do not infer owner approval.

## Expected writes

- `codex/DECISIONS/CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-2026-09-27.md`
- repository state/evidence routing only as allowed by the D2 task contract.

## Forbidden

No Composer/lockfile/dependency/CI/runtime/application/database/provider/deployment mutation.

## Exit

Commit/push the D2 decision artifact and state. Record explicit owner decision if actually obtained; otherwise record `PENDING_OWNER_DECISION` and stop for ChatGPT audit.
