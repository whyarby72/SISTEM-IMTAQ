# Change Manifest — WAKA-2C Today Session Read Model

Date: 2026-09-12  
Task: WAKA-2C  
Status: COMPLETED

## Files modified

- `application/web/app/Domains/Academic/Services/AcademicTodaySessionService.php`
- `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`
- `codex/CHANGE_IMPACTS/FIX-WAKA-2C-TODAY-SESSION-READ-MODEL-2026-09-12.md`
- `codex/CHANGE_MANIFESTS/FIX-WAKA-2C-TODAY-SESSION-READ-MODEL-2026-09-12.md`
- `codex/WORK_LOG.md`

## Read-model contract

- Today window: Asia/Jakarta calendar day, start-inclusive and next-day-exclusive.
- Primary grain: distinct `ClassSession.id`.
- Deduplication key: `class_session_id`; associated class metadata is deduplicated by class id.
- Active total: raw session status is not `CANCELLED` or `RESCHEDULED`.
- States: cancellation/reschedule raw status takes precedence; `COMPLETED` is authoritative; open `PLANNED`/`CONFIRMED` sessions are classified by planned timestamps.
- Sources: `SCHEDULED`, `EXTRA`, `AD_HOC`, and active replacement sessions are preserved as source metadata.
- No per-student, per-teacher, correction, alert, calendar, or per-class expansion is performed.

## Authorization and query safety

- Authorization delegates to `AcademicAuthorizationService`.
- The service executes one bounded `ClassSession` query for the day with constrained eager loading of class, subject, and scope-group metadata.
- No per-session or per-class database loop is used; no downstream dashboard wiring is included in this task.

## Verification

- Targeted read-model tests: 15 tests / 40 assertions / 0 failures.
- Academic regression: 238 tests / 905 assertions / 0 failures.
- PHP syntax lint: PASS.
- Pint on changed PHP files: PASS.

## Safety

Database write: NONE.  
Real data: UNCHANGED.  
Migration/schema/seed/RBAC/schedule/historical data: UNCHANGED.

## Next

Stop after WAKA-2C. Do not integrate the dashboard or begin WAKA-2D without explicit approval.
