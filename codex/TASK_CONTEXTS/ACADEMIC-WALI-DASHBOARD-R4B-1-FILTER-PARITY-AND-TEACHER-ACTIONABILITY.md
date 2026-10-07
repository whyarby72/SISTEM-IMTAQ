# Task Context — Academic Wali Dashboard R4B.1

**Task:** `ACADEMIC-WALI-DASHBOARD-R4B-1-FILTER-PARITY-AND-TEACHER-ACTIONABILITY`
**Type:** `R4B_CORRECTIVE_READ_MODEL_PATCH`
**Repository:** `whyarby72/SISTEM-IMTAQ`
**Branch:** `feat/super-admin-user-access-preferences`
**Entry HEAD:** `0e2b52b5feb5b65bb6584b0af8ff751884e8802e`

## Scope

Close the two R4B audit defects without redesigning R4B: dashboard filters
must use the same server-owned `period_state`/`needs_action` predicates as
summary counts, and `teacher_attendance_missing` may only be true when an
expected teacher participation exists, attendance is unresolved, the session
is not future, and `attendance_obligation_exists` is true.

Occurrence-pending remains actionable only for recording the occurrence. After
the occurrence is HELD, missing teacher attendance becomes actionable under the
existing attendance obligation contract.

## Required tests

- legacy 3 past due + 4 future sessions: empty/upcoming filters exclude the
  opposite set and expose exactly the expected session routes;
- all, empty, incomplete, finalized, upcoming, and needs-action filter parity:
  server predicate count = summary count = rendered session rows;
- canonical occurrence-pending teacher missing is false and urgent teacher
  missing is zero;
- after HELD, teacher missing is true and urgent teacher missing is one;
- canonical future teacher missing is false;
- existing R4B complete-dataset, backlog, empty-period, read-only, R4A1,
  R4A2, R4A2.1, and R4A2.1E regressions.

## Safety boundary

No migration, schema, dependency, runtime config, PILOT/staging/production
database access or write, AI/provider mutation, replacement-teacher governance,
Human UAT, R4C, or Grade G3. Preserve `PUBLIC_ACADEMIC_AI=OFF`,
`IMP-S12-007=NOT_STARTED`, and `SOC-MD-06`.

## Closeout routing

On exact green CI: `ACADEMIC_WALI_R4B_1_IMPLEMENTED_PASS`, with R4B P1 still
pending ChatGPT acceptance. On failure: `R4B_1_HOLD` with the exact open gate.

Next atomic task:
`RETURN_TO_CHATGPT_FOR_ACADEMIC_WALI_DASHBOARD_R4B_1_AUDIT`.
