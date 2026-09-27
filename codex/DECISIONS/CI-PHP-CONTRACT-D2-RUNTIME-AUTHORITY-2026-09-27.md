# CI-PHP-CONTRACT-D2 — Runtime Authority Decision Gate

Status: `PASS_WITH_PENDING_DECISION`  
Date: `2026-09-27`  
Repository: `whyarby72/SISTEM-IMTAQ`  
Branch: `chore/CI-PHP-CONTRACT-D2-runtime-authority-gate`  
D2 starting HEAD: `34b4dc783800961541ec2458e5f68645c77bbafa`

## Observed facts

- The CI workflow explicitly uses PHP `8.3` and installs locked dependencies.
- Local development evidence from D1 is PHP `8.5.10`; this is not deployment authority.
- `composer.json` declares PHP `^8.3`; `composer.lock` records platform PHP `^8.3`.
- Seventeen locked Symfony 8.1 packages require PHP `>=8.4.1`.
- GitHub Actions run `36301085559` fails at locked dependency installation on PHP `8.3.35`; tests are not reached.
- D1/D1R classify the blocker as `CI_PHP_LOCKFILE_COMPATIBILITY` and lockfile provenance as unresolved.

## Runtime-authority source classification

| Source | Finding | Classification |
| --- | --- | --- |
| `.github/workflows/application-foundation.yml` | CI PHP `8.3` | `AUTHORITATIVE` for CI only |
| `application/web/composer.json` | Root PHP `^8.3` | `DESCRIPTIVE` dependency contract |
| `application/web/composer.lock` | Platform PHP `^8.3` plus Symfony minimum `>=8.4.1` | `DESCRIPTIVE` and internally incompatible for PHP 8.3 |
| Deployment contract | Development → staging → production process | `AUTHORITATIVE` process model |
| Deployment contract future operations | Hosting/runtime choices are `POLICY_PENDING/IMPLEMENTATION_PENDING` | `POLICY_PENDING` |
| Policy pending register | No PHP staging/production target identified | `NO_AUTHORITY` |
| Decision log | No PHP runtime target decision | `NO_AUTHORITY` |
| Technical decisions | No PHP staging/production target decision | `NO_AUTHORITY` |
| Local PHP 8.5.10 | Developer machine only | `DESCRIPTIVE`, not deployment authority |

## Environment authority result

The repository contains no authoritative evidence identifying:

- hosting/deployment provider;
- staging provider or staging PHP version;
- production provider or production PHP version;
- container/runtime image;
- managed runtime selection;
- deployment script with a PHP runtime contract.

Therefore:

`STAGING_RUNTIME_AUTHORITY = UNRESOLVED`  
`PRODUCTION_RUNTIME_AUTHORITY = UNRESOLVED`  
`HOSTING_PROVIDER_AUTHORITY = UNRESOLVED`

## Option comparison

| Dimension | Option A: PHP >=8.4.1 | Option B: PHP 8.3 compatibility | Option C: HOLD |
| --- | --- | --- | --- |
| Current lockfile | Compatible with locked Symfony 8.1 minimums | Incompatible without controlled re-resolution | Preserves current evidence unchanged |
| Current CI | Requires later CI runtime alignment | Requires later lock/dependency remediation | Remains blocked by design |
| Dependency churn | Potentially low, but declarations may need truthfulness review | Potentially high; exact package constraints must be proven | None |
| Deployment dependency | Requires hosting/staging support for >=8.4.1 | Requires hosting/staging support for 8.3 | Defers until authority exists |
| Staging need | Mandatory runtime and regression verification | Mandatory lock re-resolution and regression verification | Authority-gathering only |
| Rollback/recovery | Runtime rollout and application rollback review | Lockfile/dependency rollback review | No remediation rollback introduced |
| Operational simplicity | Simpler dependency fit after runtime approval | More constraint management | Safest before a runtime decision |
| Evidence support now | Not approved by current authority | Not approved by current authority | Only option supported by current repository evidence |

## Decision gate

`RECOMMENDATION = OPTION_C / HOLD`

`DECISION_STATUS = PENDING_OWNER_DECISION`

`SELECTED_OPTION = NONE`

`OWNER_DECISION = NOT_OBTAINED`

No owner or management instruction in this task selects Option A or Option B. The current workflow setting alone is not institutional authority, and the local PHP version is not deployment authority. D2 therefore must not infer either option.

## Blocker and next task

`CI_PHP_LOCKFILE_COMPATIBILITY = UNRESOLVED`

Recommended next task:

`CI-PHP-CONTRACT-D2A-HOSTING-RUNTIME-AUTHORITY`

Scope: obtain and record explicit staging/production runtime authority or an owner decision selecting Option A or B. Do not implement Composer, lockfile, CI, PHP, dependency, or deployment changes in that task unless separately authorized.

## Safety boundary

D2 made no changes to Composer manifests/lockfile, CI workflow, PHP/Composer installation, dependencies/vendor, application/business source, database, migrations, provider state, OpenAI, hosting, staging, production, or deployment. This artifact records analysis and an explicit pending decision only.
