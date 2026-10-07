# Change Manifest — Academic Wali Dashboard R4A2-1E

## Identity

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WALI-DASHBOARD-R4A2-1E-TEMPORAL-JOINT-REGRESSION-EVIDENCE-CLOSURE`
- Starting HEAD: `14f10853a79b27972c001e35cfc7d47b78b36b9d`
- Branch: `feat/super-admin-user-access-preferences`
- Type: `TEST_ONLY_EVIDENCE_CLOSURE`

## Change scope

- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
  adds the exact temporal A/B joint-session partition, period-counter and
  trend-isolation regression, plus the explicit tomorrow next-session case.
- No production application source was changed.
- No migration, schema, dependency, runtime configuration, deployment, or
  business-data file was changed.
- New task contract:
  `codex/TASK_CONTEXTS/ACADEMIC-WALI-DASHBOARD-R4A2-1E-TEMPORAL-JOINT-REGRESSION-EVIDENCE-CLOSURE.md`.

## Required evidence

The new regression asserts:

- Oct 14 exposes only Class A's participant and label;
- Oct 15 exposes only Class B's participant and label;
- each authorized partition has one eligible and one resolved opportunity;
- unauthorized missing participants do not contaminate completeness;
- period class counters and aggregate counters remain isolated;
- daily trend counters remain isolated by session date;
- Oct 6 08:00 is selected as the next session at Oct 5 17:00 Asia/Jakarta.

## Validation

- PHP lint: `PASS`.
- Pint: `PASS`.
- Focused local PHPUnit: `BLOCKED_SAFE` by the existing fail-closed
  `TestDatabaseIdentityGuard` because local configuration resolves to the
  protected PILOT database; no database query or write ran.
- Disposable PostgreSQL focused/full regression: `PENDING_EXACT_CI`.
- Exact GitHub Actions result: `PENDING_EXACT_CI`.

## Safety boundary

- Production application source changed: `NO`.
- PILOT access/write: `NONE / NONE`.
- Database write: `NONE`.
- Human UAT: `DEFERRED`.
- Public Academic AI: `OFF`.
- `IMP-S12-007`: `NOT_STARTED`.
- `SOC-MD-06`: unchanged.
- R4A3, R4B, Grade G3, AI, and deployment: not started.

## Decision gate

Pending exact disposable PostgreSQL CI. Success outcome:
`ACADEMIC_WALI_R4A2_1_EVIDENCE_CLOSED`. Any application-behavior failure in
the new tests requires `R4A2_1_IMPLEMENTATION_DEFECT_FOUND` and a stop; no
production patch is authorized by this task.
