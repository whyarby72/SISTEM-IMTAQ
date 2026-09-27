# CURRENT TASK CONTEXT

**Task:** `CI-PHP-CONTRACT-R1-ALIGN-RUNTIME-UPWARD-DESIGN`  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `chore/ci-php-d2a-runtime-approval`

Owner-approved runtime authority:

- PHP family: `8.4.x`
- minimum: `8.4.1`
- applies to: CI, staging target, production target

R1 is design only. Determine the minimum safe CI/runtime-alignment change and verification plan. Do not modify workflow, Composer manifests/lockfile, dependencies, application source, runtime installation, database, provider state, or deployment.

Required output:

`codex/PLANS/CI-PHP-CONTRACT-R1-ALIGN-RUNTIME-UPWARD-DESIGN-2026-09-27.md`

The plan must define:
- exact proposed write scope;
- CI PHP target expression;
- whether composer.json should remain `^8.3` or be tightened;
- whether composer.lock should change;
- regression/test commands;
- rollback;
- staging prerequisites;
- one implementation task ID.

Recommended implementation task:
`CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84`

Commit and push the design artifact, then STOP for ChatGPT audit.
