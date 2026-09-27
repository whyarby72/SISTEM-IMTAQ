# AGENTS.md — Application Source

Root `AGENTS.md` applies. These rules are specific to application code.

## Target structure
Use a Laravel modular-monolith organization that preserves logical ownership, conceptually:

```text
application/web/app/
  Shared/
    Core/
    Platform/
    Reporting/      # future derived cross-domain layer
    AI/             # future provider/orchestrator boundary
    Communication/  # future provider-neutral messaging boundary
  Domains/
    Academic/
    Tahfizh/        # only after DESIGN_READY
    Kesantrian/     # only after DESIGN_READY
    Ruhiyah/        # only after DESIGN_READY
    Kepengasuhan/   # only after DESIGN_READY
    Bahasa/         # only after DESIGN_READY
    KegiatanKompetensi/ # only after DESIGN_READY
    AdministratifLayanan/ # only after DESIGN_READY
```

Exact namespaces may be refined in Sprint 0, but ownership boundaries are mandatory.

## Source-change safety
- Follow `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md` for every non-trivial application change.
- Use Git history/branch isolation when available; do not edit production source directly.
- Declare expected write scope before implementation and stop/reclassify if protected/shared areas become necessary.
- Use Minimum Necessary Change; unrelated refactors are separate tasks.
- Applied migrations are immutable; create new migrations for schema changes.
- Application releases must not overwrite database/uploads/official retained artifacts/secrets.
- Completed non-trivial application changes require `templates/CHANGE_MANIFEST_TEMPLATE.md`.

## Cross-module rules
- No domain-specific duplicate `Student` master/model/table.
- Domain code writes only its owned business transactions.
- Cross-domain operations use explicit service/contract interfaces.
- Do not query another module's internal tables from arbitrary controllers as a shortcut.
- Contract changes require impact analysis and consumer regression.
- Shared Core remains domain-neutral; do not hide Academic policy inside generic Core services.
- Before implementing Shared/Core identity, Guardian, Staff, Organization or shared contracts, read `docs/10_shared_core/`.
- AI implementation is future-scoped and follows `docs/08_ai/`; no provider calls inside domain models/services.
