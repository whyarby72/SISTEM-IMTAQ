# Task Context — Academic Wali Dashboard Maturity R1

Task: `ACADEMIC-WALI-DASHBOARD-MATURITY-R1-OPERATIONAL-HOME`
Type: `FULL_STACK_ROLE_SPECIFIC_DASHBOARD_MATURITY`
State: `COMPLETED / PASS / WALI_DASHBOARD_R1_IMPLEMENTED_PASS`
Branch: `feat/super-admin-user-access-preferences`
Starting HEAD: `3f0b029f3bd74a6bdae396326f668dc68adb0a11`
Tested executable HEAD: `a857e341e88dc195add3f14a9a75d11c63a6734b`
Exact CI: `37204858961` = `SUCCESS`

## Objective

Make the existing Wali Kelas dashboard an operational home while preserving
the canonical attendance transaction surface and the accepted Waka/Super
Admin dashboard behavior.

## Implemented contract

- One role-aware dashboard route remains authoritative.
- Wali class identity, grade/section, academic year/semester, and canonical
  active-student count are presented first.
- Every eligible session for the current Asia/Jakarta day is included; the
  bounded 12-session period history cannot truncate today's queue.
- Session state is exactly one of `DUE_INCOMPLETE`, `DUE_NOT_STARTED`,
  `IN_PROGRESS`, `UPCOMING`, or `FINALIZED`, with unfinished due work ranked
  first and chronological ordering inside each priority.
- Missing attendance remains unresolved work and is never treated as absence.
- Student and teacher attendance are displayed separately.
- Every session action navigates to `academic.attendance.show`; dashboard GET
  adds no business write path.
- Joint-session students are partitioned by effective class enrollment for
  the Wali's authorized class, including one-row scope-group cases.
- Safe empty states cover missing Wali assignment, no session today, missing
  roster/denominator, missing teacher participation, and no next session.
- Waka/Super Admin dashboard and AI gate behavior remain unchanged.

## Acceptance IDs

- `AWDMR1-01` strict Wali class scope and class identity
- `AWDMR1-02` canonical active-student context
- `AWDMR1-03` complete current-day queue
- `AWDMR1-04` deterministic five-state classification
- `AWDMR1-05` urgency-first ordering
- `AWDMR1-06` missing is not absent
- `AWDMR1-07` teacher-attendance separation
- `AWDMR1-08` next-session context
- `AWDMR1-09` canonical one-click session navigation
- `AWDMR1-10` joint-session class partition
- `AWDMR1-11` responsive/accessibility and empty states
- `AWDMR1-12` read-only dashboard and Waka regression

## Safety boundary

No migration, schema/config/dependency change, PILOT access/write, attendance
write, schedule/session/roster mutation, AI/provider mutation, or Public
Academic AI activation occurred. Grade G1/G2 remain accepted; G3 is
`DEFERRED_BY_OWNER_PRIORITY`, not failed or cancelled.

## Validation

- PHP syntax, Composer strict validation, Pint, Blade compile, route check,
  project structure, and diff checks: PASS.
- Local PHPUnit: fail-closed before test execution because local `.env`
  resolves to protected PILOT `imtaq`; no bypass was used.
- Disposable PostgreSQL 18.6 GitHub Actions run `37204858961`: PASS,
  15 suites, 562 warnings, 2387 assertions, 0 failed.

## Next atomic task

`RETURN_TO_CHATGPT_FOR_ACADEMIC_WALI_DASHBOARD_R1_AUDIT`

Do not start attendance UAT or resume Grade G3 automatically.
