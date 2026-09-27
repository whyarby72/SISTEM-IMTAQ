# SISTEM IMTAQ development conventions

These conventions apply to the Laravel modular monolith in `application/web/` and implement the repository safe-maintenance contracts. They govern how code changes are scoped; they do not create business policy or domain schemas.

## Application structure and ownership

The application is one Laravel project with one PostgreSQL target:

```text
app/
  Shared/       # domain-neutral platform and cross-domain contracts
  Domains/      # domain-owned business transactions
tests/
  Feature/      # workflow and boundary behavior
  Unit/         # isolated logic
database/
  migrations/  # forward-only schema history
```

Shared Core owns canonical identity and shared contracts. Domains own their transaction facts. A domain must not create a duplicate Student master or directly mutate another domain's transaction tables. Cross-domain behavior uses an explicit service/contract boundary.

## Change classification and pre-change gate

Every non-trivial change records its Task ID/Change ID, outcome, owner workstream, affected modules, source-of-truth entities, contract impact, RBAC/privacy impact, migration impact, regression scope, deployment impact and rollback path before editing.

Use the highest applicable class:

- `MODULE_INTERNAL`: one module, no published contract change;
- `MODULE_CONTRACT`: provider/consumer contract change;
- `SHARED_CORE`: canonical identity, auth/RBAC, audit or shared infrastructure;
- `CROSS_DOMAIN`: derived or integrated cross-domain behavior;
- `DATABASE_GLOBAL`: engine, global storage, migration or backup behavior;
- `SECURITY_GLOBAL`: authorization, privacy, encryption or session boundary;
- `AI_PLATFORM`, `COMMUNICATION_PLATFORM`, `PARENT_PORTAL`: only when those future boundaries are explicitly activated.

Expected write scope must be declared before implementation. If a protected zone becomes necessary, stop, reclassify impact, map consumers, and obtain the broader gate before proceeding.

## Git, change IDs and manifests

Git is the source of truth for application source and release versioning. When Git is available, use an isolated branch such as `feature/<change-id>-<short-name>` or `chore/<task-id>-<short-name>`. Commits reference the Task ID/Change ID.

Every completed non-trivial change produces a Change Manifest listing files, contracts, migrations, tests, deploy readiness and rollback. Business users are not asked to select individual files for deployment.

## Database and persistent data

Applied migrations are immutable. Schema changes use a new forward migration with compatibility and rollback/forward-fix planning. No destructive production database command is part of normal application maintenance. Persistent business data, uploads, retained artifacts and database backups remain outside replaceable code releases.

## Secrets and environment

`.env` is local and ignored; `.env.example` contains placeholders only. Secrets must not be committed, copied into prompts, or embedded in tests. Environment/config changes name variables in the Change Manifest without recording secret values.

## Testing and release path

The minimum local foundation checks are:

```bash
composer validate --strict
php artisan test
python3 scripts/check_project_structure.py
```

Regression depth follows the change class. Module-internal changes require target tests and smoke regression; contract/shared/security/global changes require consumer or negative-security regression as applicable. The normal release path is development → staging → approval → production, with a known-good commit/release and documented rollback before deployment.

## Stop conditions

Stop and escalate when scope expands into a protected zone, policy is missing, a destructive migration lacks recovery planning, a public contract lacks consumer analysis, security changes lack negative regression, the current source revision cannot be identified, or tests show a P0/P1 regression.
