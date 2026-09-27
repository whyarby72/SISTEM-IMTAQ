# CI-PHP-CONTRACT-D1 — PHP Runtime / Lockfile Compatibility Diagnosis

Status: `COMPLETED / PASS — DIAGNOSIS ONLY`  
Date: `2026-09-27`  
Branch: `chore/CI-PHP-CONTRACT-D1-diagnosis`

## 1. Exact repository state

- Repository: `whyarby72/SISTEM-IMTAQ`
- Branch: `chore/CI-PHP-CONTRACT-D1-diagnosis`
- D1 starting HEAD: `75ab00c7e91be31eb26847cb817ace2b33a98f63`
- Upstream: `origin/chore/CI-PHP-CONTRACT-D1-diagnosis`
- Worktree at diagnosis start: clean
- State-basis before D1: `6d3493bc9c8a1d013a4df944a06da6bfb3969f44`

## 2. Local runtime facts

- PHP binary: `/opt/homebrew/bin/php`
- PHP CLI: `8.5.10`
- Composer binary: `/opt/homebrew/bin/composer`
- Composer: `2.10.3`
- `composer validate --strict` with network disabled: manifest valid.
- `composer check-platform-reqs --lock`: passes on the local PHP 8.5.10 runtime.
- `composer prohibits` was not used as an authority because Composer attempted Packagist metadata access; no dependency or lockfile mutation occurred.

The local PHP 8.5 result does not prove compatibility with the CI PHP 8.3 contract.

## 3. Composer contract facts

From `application/web/composer.json`:

- root PHP constraint: `^8.3`
- Laravel constraint: `^13.17`
- `config.platform.php`: absent

From `application/web/composer.lock`:

- lock platform PHP constraint: `^8.3`
- locked Laravel: `laravel/framework v13.30.1`
- locked Laravel PHP requirement: `^8.3`

Locked packages independently requiring PHP above 8.3:

| Package | Locked version | PHP requirement |
| --- | --- | --- |
| `symfony/clock` | `v8.1.0` | `>=8.4.1` |
| `symfony/console` | `v8.1.6` | `>=8.4.1` |
| `symfony/css-selector` | `v8.1.6` | `>=8.4.1` |
| `symfony/error-handler` | `v8.1.5` | `>=8.4.1` |
| `symfony/event-dispatcher` | `v8.1.5` | `>=8.4.1` |
| `symfony/finder` | `v8.1.5` | `>=8.4.1` |
| `symfony/http-foundation` | `v8.1.6` | `>=8.4.1` |
| `symfony/http-kernel` | `v8.1.6` | `>=8.4.1` |
| `symfony/mailer` | `v8.1.5` | `>=8.4.1` |
| `symfony/mime` | `v8.1.6` | `>=8.4.1` |
| `symfony/process` | `v8.1.6` | `>=8.4.1` |
| `symfony/routing` | `v8.1.6` | `>=8.4.1` |
| `symfony/string` | `v8.1.2` | `>=8.4.1` |
| `symfony/translation` | `v8.1.5` | `>=8.4.1` |
| `symfony/uid` | `v8.1.5` | `>=8.4.1` |
| `symfony/var-dumper` | `v8.1.6` | `>=8.4.1` |
| `symfony/yaml` | `v8.1.6` | `>=8.4.1` |

The lock therefore contains a package-level requirement that cannot be satisfied by PHP 8.3, despite the root and lock platform declarations saying `^8.3`. The lockfile provenance is not proven by the adopted Git history:

`LOCKFILE_PROVENANCE = UNRESOLVED`

The resulting blocker remains `CI_PHP_LOCKFILE_COMPATIBILITY`.

## 4. CI contract facts

`.github/workflows/application-foundation.yml`:

- setup action: `shivammathur/setup-php@v2`
- PHP version: `8.3`
- dependency command: `composer install --no-interaction --prefer-dist --no-progress`
- test/verification command after install: `./scripts/verify-foundation.sh`
- contract shape: single PHP version, not a matrix

GitHub Actions run `36301085559`:

- commit: `957f062815147a0cc2ea2200fd82692c3447f3e1`
- workflow: `Application foundation`
- result: `failure`
- failing job: `foundation`
- failing step: `Install locked dependencies`
- tests were not reached
- run URL: https://github.com/whyarby72/SISTEM-IMTAQ/actions/runs/36301085559

## 5. Runtime authority findings

Classification:

- CI PHP `8.3`: `AUTHORITATIVE` for the current workflow only.
- Composer root/lock declarations `^8.3`: `DESCRIPTIVE` dependency contract; not proof of deployed runtime authority.
- Deployment model `DEVELOPMENT → STAGING → PRODUCTION`: `AUTHORITATIVE` process contract.
- Actual staging PHP version: `UNKNOWN_AUTHORITY`.
- Actual production PHP version: `UNKNOWN_AUTHORITY`.
- Hosting/provider runtime decision: `POLICY_PENDING / IMPLEMENTATION_PENDING` in the deployment contract.

No repository evidence selects PHP 8.3 or PHP >=8.4.1 as the canonical staging/production runtime. The local PHP 8.5.10 installation is development evidence only.

## 6. Root-cause explanation

The root `php: ^8.3` declaration expresses the application package constraint, but it does not guarantee that every package recorded in an already-generated lockfile remains runnable on PHP 8.3. The lock contains Symfony 8.1 packages whose own requirements are `>=8.4.1`. Composer install validates the actual runner platform against every locked package and therefore fails before tests on CI PHP 8.3.

The lock platform field also says `^8.3`, but that metadata does not repair the package-level contradiction or establish how the lock was solved. A lock resolved using a higher runtime, or produced without an effective lower platform target, can contain higher-minimum transitive packages. The exact historical solve environment is not present in authoritative repository evidence.

## 7. Remediation options — analysis only

### Option A — align runtime upward

Move the supported CI/staging/production runtime to PHP >=8.4.1 and later align declarations/documentation for truthfulness. This is technically compatible with the locked Symfony 8.1 packages, but requires an explicit hosting/staging authority decision, deployment compatibility review, and a controlled CI/staging test. Composer declarations may also need a separate truthfulness review. No part of this option was implemented by D1.

### Option B — preserve PHP 8.3 compatibility

Keep PHP 8.3 as the canonical target, then perform a separately approved dependency constraint/platform-target review and controlled lockfile re-resolution that excludes packages requiring PHP above 8.3. This has dependency churn and Laravel/Symfony compatibility risk and requires a full regression plus staging verification. No lockfile or dependency change was implemented by D1.

### Option C — defer pending runtime authority

Keep the blocker and obtain an explicit staging/production runtime decision before changing CI or dependency resolution. This is the only option supported by current repository authority because the deployment contract leaves hosting/runtime choices pending. It avoids premature dependency churn or an unsupported CI runtime claim.

## 8. Decision gate

`CI_PHP_CONTRACT_DECISION_GATE = BLOCKED_PENDING_STAGING_PRODUCTION_RUNTIME_AUTHORITY`

Current supported disposition: `CANDIDATE_C / HOLD`.

An owner/management decision must first select PHP >=8.4.1 (Option A) or intentional PHP 8.3 compatibility (Option B). D1 must not choose between them by convenience.

## 9. Recommended next task

`CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-GATE`

Scope: obtain and record explicit staging/production PHP runtime authority and select Option A or B. Do not change Composer, workflow, PHP installation, application source, or deployment until that decision is accepted.

## 10. Safety boundary

D1 made no changes to `composer.json`, `composer.lock`, GitHub Actions workflows, PHP/Composer installation, dependencies/vendor, application/business source, migrations, database state, provider state, OpenAI, or deployment. Only this diagnostic and repository-state evidence/routing are changed.
