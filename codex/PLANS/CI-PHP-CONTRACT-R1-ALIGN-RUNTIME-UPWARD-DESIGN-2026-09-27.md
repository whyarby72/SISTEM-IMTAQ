# CI-PHP-CONTRACT-R1 — Align Runtime Upward Design

Status: `COMPLETED / DESIGN_ONLY / R1R_CORRECTED`
Date: `2026-09-27`  
Branch: `chore/ci-php-r1r-constraint-correction`
Owner decision: `PHP 8.4.x; minimum 8.4.1`  
Decision source: `codex/DECISIONS/CI-PHP-CONTRACT-D2-RUNTIME-AUTHORITY-2026-09-27.md`

## Design objective

Align the repository's CI and dependency declarations with the owner-approved
PHP 8.4.x runtime authority, with PHP 8.4.1 as the minimum supported runtime.
The current locked Symfony 8.1 packages already require `>=8.4.1`.

This document is a plan only. R1 does not modify workflow, Composer files,
dependencies, runtime installation, application source, staging, production,
database, provider state, or deployment.

## Exact proposed implementation scope

The next implementation task may modify only:

1. `.github/workflows/application-foundation.yml`
   - change the single CI PHP target from `8.3` to the setup action channel
     `8.4`, followed by an executable version assertion that `PHP_VERSION >=
     8.4.1`; if the action cannot guarantee that channel, pin the exact
     approved 8.4.x patch instead;
   - preserve the existing extensions, install command, and verification step.
2. `application/web/composer.json`
   - tighten the root PHP requirement from `^8.3` to `~8.4.1` so the package
     declaration truthfully rejects unsupported PHP 8.3 runtimes;
   - do not change Laravel or unrelated package constraints.
3. `application/web/composer.lock`
   - regenerate through the approved Composer workflow after the manifest
     change, rather than hand-editing JSON;
   - preserve locked package versions unless Composer requires a minimal
     metadata/content-hash update; no dependency upgrade or downgrade is
     implied by this plan.
4. `codex/CHANGE_MANIFESTS/` and relevant repository state/evidence files
   - record the implementation change, exact resulting commit, validation,
     and rollback reference.

No other application, migration, database, provider, or deployment files are
within the proposed R2 write scope.

## Runtime target

Canonical target:

- PHP family: `8.4.x`
- minimum: `8.4.1`
- applies to: CI, staging target, production target

The implementation must verify the actual CI patch version before accepting
the channel expression. A PHP 8.4 runtime below 8.4.1 is not acceptable.

## Composer declaration decision

`composer.json` should be tightened from `^8.3` to `~8.4.1`.

Rationale:

- the owner-approved minimum is 8.4.1;
- `~8.4.1` means `>=8.4.1` and `<8.5.0`, matching the approved PHP 8.4.x
  family;
- the locked Symfony 8.1 packages require 8.4.1;
- leaving `^8.3` would falsely advertise PHP 8.3 support and permit the same
  class of lock/runtime mismatch to recur.

The Laravel constraint remains unchanged. `config.platform.php` should not be
added unless the implementation task establishes that the project needs a
separate Composer solve target; the approved runtime declaration is the first
authority.

## Lockfile decision

`composer.lock` should change only through a controlled Composer operation
after `composer.json` is tightened. The expected minimum change is lock
metadata/content hash and platform-consistency evidence; package versions
should remain unchanged unless Composer proves a compatible resolution is
required.

The implementation task must show that the resulting lock is installable on
PHP 8.4.1 and that no PHP 8.3-only compatibility claim remains.

## Required implementation validation

Run in the approved implementation environment:

1. `composer validate --strict`.
2. A read-only lock/platform check on PHP 8.4.1 or the approved CI patch.
3. `composer install --no-interaction --prefer-dist --no-progress` in a clean
   environment using the resulting lockfile.
4. `./scripts/verify-foundation.sh`.
5. Relevant AI/provider and Academic regression suites required by the change
   gate, followed by the full suite if the repository contract requires it.
6. PHP lint, Pint, and Blade view cache where applicable.
7. Git diff guard proving no application/business files outside the approved
   scope changed.
8. A CI run on the exact implementation commit.

The CI run must reach the verification step; the previous failure at locked
dependency installation must be absent.

## Staging prerequisites

Before staging promotion:

- staging PHP must be verified as PHP 8.4.x and >=8.4.1;
- hosting/runtime provider and deployment mechanism must be identified;
- clean dependency installation from the exact commit must pass;
- application boot, login/RBAC, relevant Academic smoke, and report/export
  smoke checks must pass;
- database migration state must be inspected read-only; no migration is
  implied by this runtime alignment;
- backup/restore and application rollback references must remain available;
- production remains blocked until staging evidence and approval exist.

## Rollback design

1. If CI alignment fails before deployment, revert the R2 commit as a normal
   Git release change and keep the blocker open; do not force-push or edit the
   lockfile history.
2. If staging fails, stop promotion and restore the prior known-good revision
   using the deployment contract.
3. If a PHP 8.4 runtime is unavailable, do not deploy; return to the runtime
   authority gate rather than silently falling back to PHP 8.3.
4. No database rollback is needed for this plan because no schema/data change
   is proposed.

## Decision boundary

R1 does not authorize or perform the implementation. The next task is:

`CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84`

R2 must re-confirm the exact PHP 8.4.1+ CI patch, preserve the approved file
scope, and obtain the required implementation authorization before writing
Composer/workflow files.
