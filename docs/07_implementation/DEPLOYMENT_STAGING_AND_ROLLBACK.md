# Deployment, Staging & Rollback Contract v1.0

**Status:** `DESIGN_LOCKED`  
**Authority:** companion contract to `CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`.

## 1. Target environment model

Production target uses:

`DEVELOPMENT → STAGING → PRODUCTION`

Development is where code changes/tests run. Staging is a production-like verification environment. Production is for approved operational use.

## 2. Deployment unit

A deploy is a **known repository revision/release**, not a hand-selected list of source files supplied by the business user.

Every deployment records:

- deployment/release ID;
- Git commit SHA/tag;
- target environment;
- migration list/state;
- feature-flag/config changes;
- actor/time;
- automated check result;
- smoke-test result.

## 3. Early MVP vs production automation

### Early MVP acceptable

A documented repeatable deployment script/procedure may:

1. fetch/check out approved Git revision;
2. install locked dependencies;
3. build assets;
4. run pre-deploy checks;
5. run approved migrations;
6. clear/rebuild application caches as required;
7. perform smoke check;
8. record deployment metadata.

### Production target

CI/CD should automate as much of the same process as practical, while preserving an explicit approval gate for production when appropriate.

## 4. Staging gate

A non-trivial release must not be promoted until staging evidence covers applicable items:

- application boots;
- migrations succeed;
- required automated tests pass;
- relevant role/scope access is verified;
- core user journey smoke tests pass;
- reports/rendering pass if affected;
- queue/job/provider behavior pass if affected;
- no critical DQ/security regression;
- rollback/feature-disable path remains available.

## 5. Database production safety

Before migration:

- identify whether backward compatible;
- assess table-lock/downtime risk;
- backup when risk requires;
- verify restore strategy for high-risk migration;
- prefer expand/contract evolution;
- never silently alter historical facts to fit a new schema.

`migrate:rollback` or equivalent must not be treated as universal disaster recovery. Some schema/data changes require forward-fix or database restore.

## 6. Persistent assets

Application deploy must not overwrite:

- database;
- production uploads;
- official generated artifacts intended for retention;
- backups;
- secrets/environment files.

Storage paths/buckets/volumes must be explicitly separate from disposable release code.

## 7. Production rollback options

Use the safest applicable strategy:

1. **Feature disable:** turn off the newly activated feature while keeping compatible code deployed.
2. **Application rollback:** deploy previous known-good commit/release when DB compatibility permits.
3. **Forward fix:** patch application/schema while preserving data when reverse migration is unsafe.
4. **Database restore:** only under controlled incident/recovery plan with appropriate data-loss-window assessment.

## 8. Post-deploy verification

Immediately after production deployment, run scoped smoke checks including where relevant:

- login/account access;
- backend RBAC denial/allow paths;
- changed module main transaction;
- queue/job health;
- DB migration state;
- error log/health check;
- report generation/export;
- no obvious cross-module regression.

If a P0 integrity/security smoke check fails, activate rollback/disable procedure rather than continuing normal operation.

## 9. Direct production edits prohibited

Normal operation must not use:

- editing PHP/JS source through hosting file manager;
- uncommitted server-side code fixes;
- ad-hoc copying of only guessed changed files;
- direct DB update to correct business facts outside official correction workflow.

If emergency access is unavoidable, it becomes an incident requiring immediate reconciliation into Git/change history and explicit audit; it is not a standard maintenance method.

## 10. Future production operations

Before first production go-live, finalize:

- hosting/deployment provider;
- backup frequency/retention;
- restore RTO/RPO;
- deployment approval actor;
- maintenance window rules;
- staging data/privacy strategy;
- production monitoring/error alerting;
- database migration operational SOP.

These operational choices are `POLICY_PENDING/IMPLEMENTATION_PENDING`; the safety architecture above is already binding.
