# Kesantrian / Adab & Kedisiplinan Module

**Module code:** `KESANTRIAN`  
**Current status:** `NOT_PLANNED`

Do not invent discipline scoring, sanctions, case workflow, or parent exposure rules. Observable facts and human governance only.

## Boundary rules
- Uses canonical Shared Core identities.
- Owns only its domain transactions.
- Must not write another module's transactions directly.
- Cross-domain reads/actions require an explicit contract and RBAC.
- Reports/AI are downstream consumers, not Source of Truth.

See `../MODULE_REGISTRY.md`, `../MODULE_DEPENDENCY_MAP.md`, `../CROSS_MODULE_CONTRACTS.md`, and `../CHANGE_IMPACT_RULES.md`.
