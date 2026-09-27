# CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-GATE

**Mode:** `EXISTING_REPO_ADOPTION`  
**Task type:** `RUNTIME_AUTHORITY_DECISION_GATE`  
**Owner:** `CODEX + OWNER/MANAGEMENT DECISION`  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `chore/CI-PHP-CONTRACT-D2-runtime-authority-gate`  
**State-basis before D2:** `1c8450081d7178336fbe6238be67f3291b2f3cea`

## Purpose

Resolve the authority gap identified by D1/D1R before any PHP, Composer, lockfile, CI, staging, or production remediation is allowed.

Current confirmed facts:

- CI workflow uses PHP `8.3`.
- Local development PHP observed by D1: `8.5.10`.
- `application/web/composer.json` root PHP constraint: `^8.3`.
- `application/web/composer.lock` platform PHP: `^8.3`.
- 17 locked Symfony 8.1 packages require PHP `>=8.4.1`.
- CI fails at locked dependency installation; tests are not reached.
- Staging PHP authority: unresolved.
- Production PHP authority: unresolved.
- Hosting/provider runtime authority: unresolved.
- Lockfile provenance: unresolved.
- Blocker: `CI_PHP_LOCKFILE_COMPATIBILITY`.

D2 must establish an explicit runtime authority decision or return HOLD. It must not implement the technical remediation.

## Canonical decision options

### OPTION_A — PHP >=8.4.1 becomes the target runtime authority

Meaning:

- development/staging/production target must support PHP >=8.4.1;
- later remediation may align CI to the approved runtime;
- later truthfulness review may tighten application/runtime declarations where appropriate;
- no dependency downgrade is implied.

This option is not approved merely because the current lockfile works on newer PHP.

### OPTION_B — PHP 8.3 compatibility is an institutional requirement

Meaning:

- PHP 8.3 remains an intentionally supported runtime target;
- later remediation must constrain/re-resolve dependencies to a PHP-8.3-compatible lock state;
- dependency churn and full regression/staging verification are required.

This option is not approved merely because the current workflow says 8.3.

### OPTION_C — HOLD

Meaning:

- hosting/staging/production authority is still not decided;
- CI remains blocked;
- no runtime/dependency remediation is authorized.

## Required read-only audit

### 1. Exact repository state

Verify branch, HEAD, upstream, worktree, and remote parity.

### 2. Existing runtime authority search

Use targeted repository search only.

Inspect existing sources that may carry runtime authority, including:

- `docs/07_implementation/DEPLOYMENT_STAGING_AND_ROLLBACK.md`
- `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`
- `docs/00_governance/POLICY_PENDING_REGISTER.md`
- `project_management/DECISION_LOG.md`
- `project_management/TECHNICAL_DECISIONS.md`
- `PROJECT_STATE.json`
- D1 diagnostic.

Classify each PHP/runtime-related statement as:

- `AUTHORITATIVE`
- `DESCRIPTIVE`
- `POLICY_PENDING`
- `STALE`
- `NO_AUTHORITY`

### 3. Environment evidence

Determine whether any repository evidence identifies:

- hosting/deployment provider;
- staging environment provider;
- production environment provider;
- PHP version available/selected for staging;
- PHP version available/selected for production;
- container/runtime image or managed runtime;
- deployment script/runtime constraint.

Do not infer provider or runtime from local development tools.

If no such evidence exists, record that clearly.

### 4. Technical compatibility comparison

Using existing D1 evidence only, compare Option A and Option B across:

- current lockfile compatibility;
- CI compatibility;
- likely dependency churn;
- application declaration truthfulness;
- deployment dependency;
- staging requirement;
- rollback/recovery implications;
- regression scope;
- operational simplicity;
- future maintainability.

This is analysis, not implementation.

### 5. Owner/management authority gate

D2 must produce one durable decision artifact:

`codex/DECISIONS/CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-2026-09-27.md`

The artifact must contain:

- observed facts;
- unresolved authority;
- Option A / B / C;
- technical implications;
- a recommendation field;
- explicit owner decision field;
- decision status.

Initial status may be:

`PENDING_OWNER_DECISION`

If the owner explicitly selects A, B, or C during the D2 task, record:

- selected option;
- actor = `PROJECT_OWNER` unless a more specific approved management role is known;
- decision date;
- scope;
- non-authorized implementation boundary.

Do not invent an owner decision.

### 6. Decision recording boundary

If Option A or B is explicitly approved:

- D2 may update governance/state artifacts to record the decision;
- D2 must still NOT implement runtime/dependency/CI changes;
- next task must be a narrow remediation design task.

If Option C is selected or no owner decision is obtained:

- keep blocker unresolved;
- next task must remain a governance/environment authority task, not remediation.

## Allowed writes

Only:

- `codex/DECISIONS/CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-2026-09-27.md`
- `PROJECT_STATE.json`
- `TEST_MATRIX.csv`
- `EVIDENCE_INDEX.json`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`
- `project_management/DECISION_LOG.md` only if an explicit owner decision is actually obtained and recording it there is consistent with existing decision-log practice.

## Forbidden

Do not:

- change `application/web/composer.json`;
- change `application/web/composer.lock`;
- run `composer update`;
- change GitHub Actions workflow;
- install/upgrade/downgrade PHP;
- install/upgrade/downgrade Composer;
- mutate dependencies/vendor;
- modify application/business source;
- run migrations;
- write database state;
- run OpenAI/provider actions;
- deploy;
- create staging/production infrastructure;
- select A or B without explicit owner authority;
- merge to main.

## Acceptance criteria

- `D2-AC-01` exact repository state recorded.
- `D2-AC-02` all known runtime-authority sources classified.
- `D2-AC-03` hosting/staging/production authority classified as known or unresolved.
- `D2-AC-04` Option A/B/C implications compared.
- `D2-AC-05` durable decision artifact created.
- `D2-AC-06` owner decision is either explicitly recorded or explicitly pending; never inferred.
- `D2-AC-07` blocker state is consistent with the decision.
- `D2-AC-08` next task is narrow and decision-dependent.
- `D2-AC-09` no runtime/dependency/CI/application/database/provider/deployment mutation.
- `D2-AC-10` JSON/CSV/evidence references remain valid if state files are updated.
- `D2-AC-11` branch committed/pushed and clean.

## Recommended next-task mapping

If Option A approved:

`CI-PHP-CONTRACT-R1-ALIGN-RUNTIME-UPWARD-DESIGN`

If Option B approved:

`CI-PHP-CONTRACT-R1-PRESERVE-PHP83-DESIGN`

If Option C / pending:

`CI-PHP-CONTRACT-D2A-HOSTING-RUNTIME-AUTHORITY`

These are design/remediation-planning tasks only unless separately authorized.

## Closeout

Return via repository state:

- `CI-PHP-CONTRACT-D2 = COMPLETED / PASS`, `PASS_WITH_PENDING_DECISION`, or `HOLD`;
- exact owner decision state;
- blocker state;
- one next task;
- `STOP = YES`.

ChatGPT will audit the pushed decision artifact and state directly from GitHub.
