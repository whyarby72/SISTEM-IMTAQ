# Tahfizh Module

**Module code:** `TAHFIZH`  
**Current status:** `NOT_PLANNED`

Do not implement halaqah/ziyadah/murajaah/tasmi transactions until a Tahfizh planning bundle is approved.

## Boundary rules
- Uses canonical Shared Core identities.
- Owns only its domain transactions.
- Must not write another module's transactions directly.
- Cross-domain reads/actions require an explicit contract and RBAC.
- Reports/AI are downstream consumers, not Source of Truth.

See `../MODULE_REGISTRY.md`, `../MODULE_DEPENDENCY_MAP.md`, `../CROSS_MODULE_CONTRACTS.md`, and `../CHANGE_IMPACT_RULES.md`.
