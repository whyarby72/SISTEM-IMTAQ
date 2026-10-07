# Task Context — Academic Wali R4A2-1E

## Identity

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WALI-DASHBOARD-R4A2-1E-TEMPORAL-JOINT-REGRESSION-EVIDENCE-CLOSURE`
- Type: `TEST_ONLY_EVIDENCE_CLOSURE`
- Starting HEAD: `14f10853a79b27972c001e35cfc7d47b78b36b9d`
- Branch: `feat/super-admin-user-access-preferences`

## Scope

Add executable regression evidence for the R4A2-1 temporal joint-session defect:

- Oct 14 uses the Class A partition before the end-exclusive transition;
- Oct 15 uses the Class B partition on the transition date;
- unauthorized participants and class labels are absent from each Wali read model;
- period counters and daily trends remain partition-isolated;
- a fixed Oct 5 17:00 Asia/Jakarta clock exposes the Oct 6 08:00 next session.

Production application source, migrations, schema, runtime configuration,
PILOT data, and human UAT remain out of scope. The tests must use the existing
disposable PostgreSQL foundation workflow and must not bypass
`TestDatabaseIdentityGuard`.

## Required validation

- PHP lint and Pint for the changed test;
- focused R4A2-1E PHPUnit tests on disposable PostgreSQL;
- relevant Academic dashboard regression;
- full foundation verification and exact GitHub Actions success;
- no production application source change;
- no PILOT access or write.

## Decision gate

`ACADEMIC_WALI_R4A2_1_EVIDENCE_CLOSED` only after all five evidence cases pass
on disposable PostgreSQL and exact-current GitHub Actions is green. If a new
test fails because production behavior is incorrect, stop with
`R4A2_1_IMPLEMENTATION_DEFECT_FOUND`; do not weaken assertions or patch
production behavior in this test-only task.

## Next action

Return to ChatGPT/project owner for R4A2-1E audit. Do not start R4A3, R4B,
Grade G3, human UAT, PILOT work, or AI work automatically.
