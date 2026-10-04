# Change Manifest — Academic Wali Dashboard Maturity R1

Date: 2026-10-04
Task: `ACADEMIC-WALI-DASHBOARD-MATURITY-R1-OPERATIONAL-HOME`
Branch: `feat/super-admin-user-access-preferences`
Starting HEAD: `3f0b029f3bd74a6bdae396326f668dc68adb0a11`
Tested executable HEAD: `a857e341e88dc195add3f14a9a75d11c63a6734b`
Exact CI: `37204858961` = `SUCCESS`

## Previous weaknesses

- Period analytics appeared before the Wali's immediate work.
- The session list was a chronological period list capped at 12, so it was not
  a reliable complete current-day queue.
- Work state did not distinguish upcoming, in-progress, due-not-started,
  due-incomplete, and finalized sessions.
- Class identity, next-session context, urgency counts, and per-session teacher
  attendance were not presented as an operational home.
- Joint partitioning skipped the one-scope-group case.

## Implemented result

- Added a Wali-only operational read model with canonical class context,
  current Asia/Jakarta day boundaries bound as UTC instants, all current-day
  sessions, deterministic state/priority, next session, urgent counts, and
  due-work completion.
- Added participant opportunity, resolved, missing, and completeness values
  without converting missing into absence.
- Added separate expected-primary teacher attendance status.
- Added an operational-first responsive Blade hierarchy: class identity,
  urgent summary, today's queue, next session, then period summary/analytics.
- Preserved the existing period filter and bounded period history below the
  operational surface.
- All session CTAs use the canonical `academic.attendance.show` route.
- Added focused regression for state ordering, missing semantics, >12 today
  sessions, GET read-only behavior, responsive/accessibility markup, and safe
  no-assignment state. Existing joint, authorization, attendance, Waka, and
  full foundation regressions remain green.

## Executable files

- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`

## Verification

- Composer validation, PHP lint, Pint, Blade cache, route inspection, project
  structure, and `git diff --check`: PASS.
- Local database tests correctly stopped at the protected PILOT identity
  guard; no local database access or bypass occurred.
- First exact-head run `37204711018` found one over-broad negative string
  assertion only; production behavior was not implicated.
- Exact tested executable run `37204858961` on
  `a857e341e88dc195add3f14a9a75d11c63a6734b`: SUCCESS; PostgreSQL 18.6,
  migration-from-zero, schema/identity checks, 15 suites, 562 warnings,
  2387 assertions, 0 failed.

## Safety and rollback

No business write path, migration, schema/config/dependency change, PILOT
query/write, attendance fact, schedule/session/roster mutation, AI/provider
change, or deployment occurred. Public Academic AI remains OFF. Rollback is a
Git revert of the two executable commits; no data rollback is required.

Grade G1 and G2 remain PASS. Grade G3 is `DEFERRED_BY_OWNER_PRIORITY`.
Academic Web completion remains `4/10 = 40% COMPLETE_EVIDENCED`; this maturity
work improves an already-evidenced dashboard capability without changing the
review denominator.

## Decision

`WALI_DASHBOARD_R1_IMPLEMENTED_PASS`

Next: `RETURN_TO_CHATGPT_FOR_ACADEMIC_WALI_DASHBOARD_R1_AUDIT`.
Recommended subsequent Wali-dashboard task after audit:
`ACADEMIC-WALI-DASHBOARD-MATURITY-R2-CONTROLLED-USABILITY-REVIEW`.

Do not start attendance UAT or resume Grade G3 automatically.
