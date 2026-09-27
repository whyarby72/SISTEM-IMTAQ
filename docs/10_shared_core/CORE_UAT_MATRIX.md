# Shared Core UAT / Automated Test Matrix v1.0

P0 = integrity/security gate. P1 = core workflow gate.

| ID | Scenario | Expected | Pri |
|---|---|---|---|
| CORE-UAT-001 | Create Student | one canonical Student + permanent code | P0 |
| CORE-UAT-002 | Two students same name | allowed; IDs distinct | P0 |
| CORE-UAT-003 | Missing NIS/NISN | Student creation allowed | P1 |
| CORE-UAT-004 | Placeholder NISN `0000` | rejected/normalized missing | P0 |
| CORE-UAT-005 | Add NISN later | same Student, new identifier | P0 |
| CORE-UAT-006 | Duplicate active NISN | block | P0 |
| CORE-UAT-007 | Correct NISN | old superseded; new active; audit | P0 |
| CORE-UAT-008 | Identifier correction creates Student | must not happen | P0 |
| CORE-UAT-009 | Student exit | history preserved | P0 |
| CORE-UAT-010 | Exit followed by future domain eligibility check | future eligibility false per effective date | P0 |
| CORE-UAT-011 | Historical fact before exit | remains referenceable | P0 |
| CORE-UAT-012 | Overlapping student statuses | block | P0 |
| CORE-UAT-013 | Rename Student | Student_ID unchanged + audit | P1 |
| CORE-UAT-014 | Delete Student with history | normal destructive delete blocked | P0 |
| CORE-UAT-015 | Create Guardian | canonical guardian code created | P1 |
| CORE-UAT-016 | Same Guardian linked to 2 children | one Guardian, two relationships | P0 |
| CORE-UAT-017 | Relationship type stored on Guardian row | must not be required/canonical | P0 |
| CORE-UAT-018 | End one child relationship | other child relationship unaffected | P0 |
| CORE-UAT-019 | Unauthorized guardian for Student | not returned by recipient context | P0 |
| CORE-UAT-020 | Change guardian phone | old history retained; new active | P1 |
| CORE-UAT-021 | Verified phone but no consent | communication eligibility still evaluated separately | P0 |
| CORE-UAT-022 | Ordinary teacher requests guardian raw contact | deny unless explicit permission | P0 |
| CORE-UAT-023 | Create Staff | stable staff code | P1 |
| CORE-UAT-024 | End Staff employment/assignment | historical references retained | P0 |
| CORE-UAT-025 | Staff title change | RBAC not silently changed | P0 |
| CORE-UAT-026 | Disable User account | Staff identity remains | P0 |
| CORE-UAT-027 | User without role permission calls protected Core command | deny backend | P0 |
| CORE-UAT-028 | Expired role assignment | authorization denied | P0 |
| CORE-UAT-029 | Dynamic domain scope changes | scope follows effective assignment | P0 |
| CORE-UAT-030 | Technical admin attempts business approval without permission | deny | P0 |
| CORE-UAT-031 | Organization parent cycle | block | P0 |
| CORE-UAT-032 | Historical staff assignment after unit closes | history remains resolvable | P1 |
| CORE-UAT-033 | High-value identifier/status change | audit contains old/new/reason/actor | P0 |
| CORE-UAT-034 | Audit entry includes secret value | test must fail / secret redacted | P0 |
| CORE-UAT-035 | Concurrent Student identity edits same version | one succeeds; stale update conflicts | P0 |
| CORE-UAT-036 | Repeated idempotent import row | no duplicate Core master | P0 |
| CORE-UAT-037 | Name-only legacy match | no automatic merge | P0 |
| CORE-UAT-038 | Domain tries direct Core write bypass command | architecture/repository policy test rejects path | P0 |
| CORE-UAT-039 | Core status event consumer fails | Core commit behavior follows defined transactional/outbox/job strategy; no silent partial cross-domain direct writes | P1 |
| CORE-UAT-040 | Parent recipient contract returns report-ineligible guardian | must exclude | P0 |

## Required automated suites
At minimum automate:
- uniqueness/temporal constraints;
- Student identifier lifecycle;
- Student status effective dating;
- guardian relationship authorization;
- RBAC negative tests;
- audit presence for high-value commands;
- concurrency/version checks;
- organization cycle prevention;
- contract serialization/data minimization.
