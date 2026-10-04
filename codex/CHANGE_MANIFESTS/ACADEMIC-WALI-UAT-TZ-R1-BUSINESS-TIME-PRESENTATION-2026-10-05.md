# Change Manifest — ACADEMIC-WALI-UAT-TZ-R1

## Task

`ACADEMIC-WALI-UAT-TZ-R1-BUSINESS-TIME-PRESENTATION`

Decision: `ACADEMIC_WALI_TIMEZONE_R1_IMPLEMENTED_PASS`.

## Root cause

PostgreSQL `timestamptz` values are stored and queried as UTC instants, while
several Academic presentation paths formatted the model Carbon value without
converting it to the business timezone. A session stored as `01:00–02:30 UTC`
was therefore rendered as `01:00–02:30` instead of `08:00–09:30 Asia/Jakarta`.

## Canonical contract

- Storage and technical SQL comparison remain UTC/absolute instants.
- Academic business and user-facing presentation timezone is
  `config('academic.business_timezone', 'Asia/Jakarta')`.
- Local-day query boundaries are still constructed in the business timezone,
  then converted to UTC for SQL binding.
- Stored timestamps are not rewritten.

## Implementation scope

- Added `App\Shared\Platform\Presentation\AcademicBusinessTime` as the
  narrow reusable business-time presentation boundary.
- Added `academic.business_timezone` configuration with the Asia/Jakarta
  default.
- Updated Wali dashboard session cards and period session history to format
  session times after business-time conversion.
- Updated attendance detail, review, and exception presentation paths.
- Updated Wali/session scope date derivation to use the same business-time
  boundary.
- Updated Today/dashboard service boundary configuration to use the explicit
  Academic business timezone.
- Added deterministic unit coverage for UTC-to-business conversion and the
  local-date boundary without mutating the stored value.
- Updated Academic UI fixtures to use explicit UTC instants corresponding to
  the intended Asia/Jakarta business labels, so their expected presentation
  remains deterministic.
- Added dashboard and attendance UI assertions for the business-time session
  presentation.

## Files changed

- `application/web/app/Shared/Platform/Presentation/AcademicBusinessTime.php`
- `application/web/config/academic.php`
- `application/web/app/Domains/Academic/Services/AcademicAuthorizationService.php`
- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Domains/Academic/Services/AcademicTodaySessionService.php`
- `application/web/app/Domains/Academic/Services/JointAttendanceRosterBreakdownService.php`
- `application/web/app/Domains/Academic/Services/SessionAttendanceScopeResolver.php`
- `application/web/app/Domains/Academic/Services/SessionParticipantSnapshotter.php`
- `application/web/app/Domains/Academic/Services/WaliKelasContextResolver.php`
- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/resources/views/academic/attendance/show.blade.php`
- `application/web/resources/views/academic/attendance/reviews.blade.php`
- `application/web/resources/views/academic/attendance/exceptions.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `application/web/tests/Unit/AcademicBusinessTimeTest.php`

## Safety boundary

- No migration or schema change.
- No timestamp rewrite.
- No attendance, roster, session, lock, or correction write authorized.
- No AI/provider/deployment change.
- Public Academic AI remains OFF.
- Grade G3 remains deferred.

## Verification evidence

- `composer validate --strict`: PASS.
- Focused `AcademicBusinessTimeTest`: PASS (2 tests, 11 assertions).
- Pint on all changed PHP/config files: PASS.
- `git diff --check`: PASS.
- `php artisan view:cache`: PASS.
- Browser presentation check: Wali dashboard and selected attendance page
  render the candidate session as `08:00–09:30` on 5 October 2026.
- Feature tests requiring Laravel's test database were fail-closed by the
  local protected-pilot identity guard; they were not bypassed and did not
  run against the pilot as a test database. Disposable PostgreSQL CI remains
  required for the full acceptance gate.
- `python3 scripts/check_project_structure.py`: PASS.
- Exact disposable-PostgreSQL GitHub Actions run `37241464340`:
  SUCCESS on commit `06bd80e56d4232ddd8f2beca61a607e09f268bdc`; 17 passed,
  575 warnings, 2,464 assertions, 0 failed.

## Recovery

The change is presentation/config/service-read-scope only. Revert this
manifest's implementation commit as one unit if the CI gate or UAT review
rejects the result. Do not alter stored timestamps or applied migrations.

## Next atomic task

Return this closeout for ChatGPT audit. Do not resume human attendance input
automatically.
