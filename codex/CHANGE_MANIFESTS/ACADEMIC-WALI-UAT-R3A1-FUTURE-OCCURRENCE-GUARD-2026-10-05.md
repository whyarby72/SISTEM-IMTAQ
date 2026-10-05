# Change Manifest — ACADEMIC-WALI-UAT-R3A1-FUTURE-OCCURRENCE-GUARD

## Identity

- Project: `SISTEM-IMTAQ`
- Branch: `feat/super-admin-user-access-preferences`
- Starting HEAD: `6cfd0284b73153c27bb6c5667aeb49465759651b`
- Tested executable HEAD: `7442dac3466d4abfca5bc0c17a80e8e6db713234`
- Exact GitHub Actions: `37245865829` — `SUCCESS`
- Final governance HEAD: pending this manifest commit

## Scope and decision

This change closes the R3A1 temporal guard for canonical ClassSession
attendance. A future session is server-resolved as `UPCOMING`; at or after
`planned_start_at`, a null or `SCHEDULED` occurrence is
`OCCURRENCE_PENDING`. `HELD`/`PARTIAL_HELD` cannot be recorded or corrected
before the planned start instant. Waka physical cancel/reschedule authority
remains available for future sessions, while routine occurrence and attendance
input remain unavailable until the session starts.

Decision at tested executable HEAD: `R3A1_IMPLEMENTED_PASS`.

## Implementation

- `AcademicSessionExecutionStateResolver` now compares absolute UTC instants,
  maps future canonical sessions to `UPCOMING`, and maps started `SCHEDULED`
  occurrences to `OCCURRENCE_PENDING`.
- `CanonicalSessionOccurrenceService` rejects pre-start `HELD` writes inside
  its locked transaction before occurrence or participant-snapshot mutation;
  the same guard applies to correction to `HELD`. `PARTIAL_HELD` uses the same
  canonical `HELD` guard.
- `StudentAttendanceController` keeps Waka physical cancel/reschedule actions
  separate from routine occurrence actions, and suppresses teacher attendance
  recording when the scope is locked.
- Attendance UI presents the server-owned future state and does not expose
  routine occurrence or student/teacher attendance input before start.
- Dashboard read models use `Akan datang` / `Lihat Sesi` and exclude future
  canonical sessions from occurrence-pending, attendance-due, and teacher-
  missing counters.

## Changed files

Application/source and UI:

- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Domains/Academic/Services/AcademicSessionExecutionStateResolver.php`
- `application/web/app/Domains/Academic/Services/CanonicalSessionOccurrenceService.php`
- `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`
- `application/web/resources/views/academic/attendance/show.blade.php`

Tests:

- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `application/web/tests/Feature/Academic/CanonicalAttendanceSemanticContractTest.php`
- `application/web/tests/Feature/Academic/CanonicalSessionOccurrenceServiceTest.php`
- `application/web/tests/Feature/Academic/StudentAttendanceUiTest.php`

This manifest is the only governance artifact added after the tested
executable commit.

## Acceptance evidence

- Future null and `SCHEDULED` canonical states: `UPCOMING`; no occurrence
  action, attendance obligation, or attendance input.
- At-start null and `SCHEDULED` states: `OCCURRENCE_PENDING`; occurrence action
  is required; attendance input remains unavailable until `HELD`.
- Pre-start `HELD`, `PARTIAL_HELD`, and correction to `HELD` are rejected with
  the controlled temporal error and no occurrence/snapshot mutation.
- At/after-start `HELD` and `PARTIAL_HELD` remain allowed.
- Wali future routine actions are denied; Waka future physical controls remain
  visible and authorized; Waka future routine `HELD` is denied.
- Legacy sessions, joint-session behavior, finalization, historical
  acknowledgement, and timezone handling remain covered by the regression
  suite.
- No new database writes were performed by this task. CI used disposable
  PostgreSQL only.

## Verification

Static checks completed locally:

- PHP lint: PASS for changed PHP files.
- Pint `--test`: PASS for changed PHP files.
- Blade/view cache: PASS on the executable implementation checkpoint.
- Project structure check: PASS.
- `git diff --check`: PASS.

Local PHPUnit was not run against the protected local PILOT database; the
test database identity guard rejected that target, so no PILOT query or write
was performed. Disposable PostgreSQL foundation verification on exact
executable HEAD passed in GitHub Actions run `37245865829` with 0 failures.

## Explicit boundaries

- Database business write: `NONE`.
- PILOT access/write: `NONE / NONE`.
- Migration/schema change: `NONE`.
- Dependency/runtime/config change: `NONE`.
- AI/provider change or OpenAI request: `NONE`.
- Public Academic AI: `OFF`.
- `SOC-MD-06`: unchanged.
- `IMP-S12-007`: `NOT_STARTED`.
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`.
- Academic Web progress: `4/10 = 40% COMPLETE_EVIDENCED`.

## Handoff

- `SAFE_TO_CLOSE = YES` for R3A1 implementation and audit.
- `NEXT_ATOMIC_TASK = R3A1_SOURCE_READY_FOR_CHATGPT_AUDIT`.
- Do not automatically start human UAT, R3B, or Grade G3.
