# Documentation Index — v1.12 Multi-Module

## Shared/system governance
- `00_governance/`
- root `modules/` architecture registry/dependencies/contracts/change-impact rules
- root `shared/` shared workstream boundaries

## Academic active implementation contract
- `01_scope/`
- `02_architecture/` (including `CLASS_MASTER_AND_ENROLLMENT.md` and `SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`)
- `03_operations/`
- `04_analytics/`
- `05_migration/`
- `06_testing/`
- `07_implementation/` — includes authoritative Codex safe-change/maintenance and deployment/staging/rollback contracts.

These directories are currently **Academic-specific** even though they live under `docs/`. Do not apply Academic attendance/grade/report rules to another module without that module's own approved plan.

## Shared AI future architecture
- `08_ai/`
- Initial AI access: `SUPER_ADMIN` only via RBAC; see `08_ai/AI_RBAC_ACCESS_POLICY.md`.

Future module-specific documentation belongs under `modules/<module>/docs/` or another explicitly registered authoritative path. Avoid duplicating active specs in multiple locations.

## Shared Communication / WhatsApp future architecture
- `09_communication/`

This is a shared delivery layer, not an Academic-owned transport implementation. It covers Parent delivery plus institutional broadcasts, reminders, action requests, audience/group resolution and future teacher availability confirmation.

## Shared Core authoritative architecture
- `10_shared_core/`

Use this for Student, Guardian, Staff, Organization, identity lifecycle and Core↔Platform/shared-contract implementation.

## Shared Parent Portal future architecture
- `11_parent_portal/`

This is a Guardian-facing read/access surface. It consumes Shared Core identity, Shared Platform auth/RBAC, source-domain published artifacts and optional Shared Communication deep links. It is not a transaction Source of Truth.
