# Shared Core Specification — Index

This directory is the authoritative design contract for the shared identity/governance foundation consumed by every SISTEM IMTAQ domain.

Read in this order:
1. `SHARED_CORE_ARCHITECTURE.md`
2. `CORE_DATA_MODEL.md`
3. `CORE_POSTGRESQL_CONTRACT.md`
4. `IDENTITY_LIFECYCLE.md`
5. `GUARDIAN_MASTER.md`
6. `STAFF_ORGANIZATION.md`
7. `AUTH_RBAC_AUDIT.md`
8. `CORE_SERVICE_CONTRACTS.md`
9. `CORE_DATA_QUALITY.md`
10. `CORE_PRIVACY_SECURITY.md`
11. `CORE_UAT_MATRIX.md`
12. `CORE_IMPLEMENTATION_ROADMAP.md`

## Boundary rule
Shared Core owns canonical person/organization identity and common relationships. Shared Platform owns authentication runtime, authorization infrastructure, audit infrastructure, queues/jobs, secrets and technical operations. This specification defines how they integrate; it does **not** turn Shared Core into a god-module.

## North Star
`ONE STUDENT → ONE ID → ONE HISTORY → MANY ACTIVITIES → ONE SOURCE OF TRUTH → MANY REPORTS.`
