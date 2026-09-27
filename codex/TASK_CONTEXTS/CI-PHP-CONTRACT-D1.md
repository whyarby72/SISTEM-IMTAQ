# CI-PHP-CONTRACT-D1 — PHP Runtime / Lockfile Compatibility Diagnosis

**Mode:** `EXISTING_REPO_ADOPTION`  
**Task type:** `READ_ONLY_DIAGNOSIS`  
**Owner:** `CODEX`  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `chore/CI-PHP-CONTRACT-D1-diagnosis`  
**State-basis before D1:** `6d3493bc9c8a1d013a4df944a06da6bfb3969f44`

## Problem

The exact baseline GitHub Actions run for SISTEM IMTAQ fails before tests at Composer dependency installation.

Known evidence:

- GitHub Actions run: `36301085559`
- failing step: `composer install --no-interaction --prefer-dist --no-progress`
- CI runner PHP: `8.3.35`
- `application/web/composer.json` root PHP constraint: `^8.3`
- `application/web/composer.json` Laravel constraint: `^13.17`
- `application/web/composer.lock.platform.php`: `^8.3`
- lockfile includes multiple Symfony 8.1 packages with `php >=8.4.1`
- workflow `.github/workflows/application-foundation.yml` explicitly sets `php-version: '8.3'`

Current blocker:

`CI_PHP_LOCKFILE_COMPATIBILITY`

D1 must determine the actual runtime/dependency contract and identify the narrowest safe remediation path. D1 MUST NOT implement that remediation.

## Decision question

Determine which statement is supported by evidence:

### Candidate A — runtime target moved upward

The intended canonical runtime is now PHP `>=8.4.1`, so CI/runtime declarations are stale.

### Candidate B — PHP 8.3 compatibility remains intentional

The intended canonical runtime still includes PHP 8.3, so the current lockfile dependency selection is incompatible with the intended runtime contract and must later be regenerated/constrained safely.

### Candidate C — insufficient authority

Repository/local evidence is not enough to choose A or B because staging/production runtime authority is not yet finalized.

D1 may recommend a **decision gate**, but must not silently choose A/B from convenience alone.

## Required read-only audit

### 1. Exact Git state

Verify and record:

- repository root;
- current branch;
- exact HEAD;
- upstream;
- clean/dirty worktree.

If worktree is dirty before D1, STOP unless the changes are only the expected D1 routing commits already pushed and local/remote parity is proven.

### 2. Local runtime facts

Record, without modifying configuration:

- `php -v`
- PHP binary path
- `composer --version`
- Composer binary path
- relevant loaded PHP version only; do not dump secrets/environment.

Do not install or upgrade PHP/Composer.

### 3. Composer contract facts

Inspect:

- `application/web/composer.json`
- `application/web/composer.lock`

Record at minimum:

- root PHP constraint;
- Laravel constraint;
- lockfile platform PHP constraint;
- exact locked Laravel version;
- every locked package that independently requires PHP above 8.3, with package/version/require.php;
- whether `config.platform.php` exists in composer.json;
- whether lockfile was solved in a way that permits PHP 8.4-only packages despite root `^8.3`.

Use Composer diagnostic commands only if read-only and deterministic. Suitable examples where available:

- `composer validate --strict`
- `composer check-platform-reqs --lock`
- `composer prohibits php 8.3.35 --locked` or equivalent supported syntax
- `composer show --locked <package>`

Do not run `composer update`, including partial updates.
Do not rewrite `composer.lock`.
Do not run a command that modifies installed dependencies.

### 4. CI contract facts

Inspect:

- `.github/workflows/application-foundation.yml`
- any other workflow/runtime declaration that actually exists.

Record:

- PHP version(s);
- dependency-install command;
- test command;
- whether CI is a single-version contract or matrix;
- exact reason tests are currently skipped/fail to start.

Do not edit workflow files.

### 5. Repository-wide PHP/runtime authority search

Use targeted search, not bulk reading.

Find authoritative or relevant mentions of:

- PHP 8.3;
- PHP 8.4;
- minimum PHP version;
- runtime/hosting requirements;
- staging runtime;
- production runtime;
- Composer platform configuration.

Classify each result:

- `AUTHORITATIVE`
- `DESCRIPTIVE`
- `STALE`
- `UNKNOWN_AUTHORITY`

Do not silently treat README text as stronger than locked governance/deployment contracts.

### 6. Environment / deployment authority

Inspect only relevant existing deployment/governance docs, including:

- `docs/07_implementation/DEPLOYMENT_STAGING_AND_ROLLBACK.md`
- `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`
- `PROJECT_STATE.json`
- any existing environment/deployment evidence found by targeted search.

Determine whether an actual staging or production PHP version is currently authoritative.

If hosting/provider/runtime is still `POLICY_PENDING` or `IMPLEMENTATION_PENDING`, state that explicitly.

Do not invent a production runtime.

### 7. Historical provenance

Because Git history begins from the adopted baseline, do not assume Git can prove how the lockfile was originally generated.

Check only targeted durable repo evidence/recovery artifacts if they can answer:

- whether PHP 8.4 was intentionally adopted;
- whether Composer/Laravel dependency updates were intentionally performed;
- whether prior local test evidence ran under a known PHP version.

If provenance cannot be proved, record:

`LOCKFILE_PROVENANCE = UNRESOLVED`

Do not bulk-read recovery archives.

### 8. Compatibility explanation

Produce a precise technical explanation of why this state can exist:

- root `php: ^8.3` does not by itself guarantee every locked package remains runnable on PHP 8.3;
- a lock solved on a higher allowed PHP runtime can select transitive packages with higher minimum PHP requirements unless a lower platform target/constraints enforce compatibility.

Confirm this explanation against actual repository/Composer evidence before using it as a conclusion.

### 9. Remediation options — analysis only

Compare, but DO NOT implement:

#### Option A — align runtime upward
Potential future changes may include CI and documented runtime target to PHP >=8.4.1.

Assess:
- compatibility risk;
- deployment/hosting dependency;
- test scope;
- rollback implications;
- whether composer.json PHP constraint should later be tightened for truthfulness.

#### Option B — preserve PHP 8.3 compatibility
Potential future changes may include platform-target enforcement and a controlled lockfile re-resolution compatible with PHP 8.3.

Assess:
- dependency churn risk;
- Laravel/Symfony compatibility;
- test scope;
- need for exact package constraints or Composer platform config;
- rollback implications.

#### Option C — defer until hosting/runtime authority exists
Assess whether the correct governance outcome is to preserve blocker/HOLD rather than mutate dependencies prematurely.

Do not produce a dependency update command as an implementation instruction in D1.

## Required output artifact

Create:

`codex/DIAGNOSTICS/CI-PHP-CONTRACT-D1-2026-09-27.md`

If `codex/DIAGNOSTICS/` does not exist, creating that directory via this file is allowed.

The diagnostic must contain:

1. exact repo/branch/HEAD;
2. observed local PHP/Composer facts;
3. composer.json/lock facts;
4. CI facts;
5. staging/production authority findings;
6. lockfile provenance status;
7. root-cause explanation;
8. A/B/C option comparison;
9. decision gate;
10. recommended **next task**, not implementation;
11. explicit statement that D1 made no runtime/dependency/source/database/provider changes.

## Repository-state updates allowed at closeout

After diagnosis, update only if facts genuinely changed:

- `PROJECT_STATE.json`
- `TEST_MATRIX.csv`
- `EVIDENCE_INDEX.json`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`

Do not touch application source/config/runtime files.

## Acceptance criteria

- `D1-AC-01` exact branch/HEAD/worktree state recorded.
- `D1-AC-02` local PHP and Composer versions recorded without secret leakage.
- `D1-AC-03` root PHP/Laravel constraints and lockfile platform constraints recorded.
- `D1-AC-04` all relevant locked packages requiring PHP >8.3 identified.
- `D1-AC-05` CI PHP contract and exact failure stage recorded.
- `D1-AC-06` staging/production runtime authority classified as known or unresolved with evidence.
- `D1-AC-07` lockfile provenance classified with evidence; no guessing.
- `D1-AC-08` technical root cause explained accurately.
- `D1-AC-09` Options A/B/C compared without implementing any.
- `D1-AC-10` one explicit management/architecture decision gate produced.
- `D1-AC-11` no composer.json/lock/workflow/application source mutation.
- `D1-AC-12` no Composer update/install mutation, package install, PHP upgrade, migration, DB write, OpenAI/provider action, or deployment.
- `D1-AC-13` diagnostic artifact committed/pushed.
- `D1-AC-14` next task is narrow and depends on the D1 decision result.

## Forbidden in D1

Do not:

- run `composer update`;
- change `composer.json`;
- change `composer.lock`;
- change GitHub Actions workflow;
- install/upgrade/downgrade PHP;
- install/upgrade/downgrade Composer;
- mutate Laravel/vendor dependencies;
- modify application source;
- run migrations;
- write database state;
- run live OpenAI verification;
- change provider/public-AI state;
- deploy;
- merge into `main`;
- resolve the blocker by convenience.

## Stop conditions

STOP with `HOLD` if:

- local repository state cannot be reconciled with the remote branch;
- a diagnostic command would mutate dependencies or runtime;
- production/staging runtime authority is required but absent;
- selecting A or B requires owner/management approval;
- evidence conflicts materially.

## Closeout

Return via repository state:

- `CI-PHP-CONTRACT-D1 = COMPLETED / PASS` if diagnosis is complete;
- blocker status remains unresolved unless D1 evidence itself resolves the contract;
- one recommended next task ID;
- `STOP = YES`.

ChatGPT will audit the pushed diagnostic directly from GitHub.
