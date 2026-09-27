# Akademik Module

**Module code:** `ACADEMIC`  
**Current status:** `DESIGN_READY`

`docs/01_scope` through `docs/07_implementation` are the current authoritative Academic specifications. `codex/TASK_QUEUE.md` is the active Academic delivery queue.

## Boundary rules
- Uses canonical Shared Core identities.
- Owns only its domain transactions.
- Must not write another module's transactions directly.
- Cross-domain reads/actions require an explicit contract and RBAC.
- Reports/AI are downstream consumers, not Source of Truth.

See `../MODULE_REGISTRY.md`, `../MODULE_DEPENDENCY_MAP.md`, `../CROSS_MODULE_CONTRACTS.md`, and `../CHANGE_IMPACT_RULES.md`.

## Class structure
Canonical future-proof class model: `../../docs/02_architecture/CLASS_MASTER_AND_ENROLLMENT.md`. Grade level and section/rombel are separate; section codes are data-driven and classes are academic-year-specific.


## Schedule conflict integrity
Canonical scheduling-integrity contract: `../../docs/02_architecture/SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`. Teacher/class overlap is a hard backend block across recurring rules, sessions and schedule changes; preflight warnings are advisory only.
