# Change Impact — WAKA-2C Today Session Read Model

Date: 2026-09-12  
Scope: Academic operational Today read model only

## Implemented

- Added `AcademicTodaySessionService` as a backend-only, institution-grained read model.
- Uses the Asia/Jakarta calendar window `[start_of_today, start_of_next_day)` and one `ClassSession` item per teaching event.
- Class-session state precedence is explicit: `CANCELLED`, `RESCHEDULED`, `COMPLETED`, `OVERDUE_UNFINISHED`, `IN_PROGRESS`, `UPCOMING`.
- Joint sessions expose deduplicated associated classes without expanding into per-class institutional items.
- Reuses `AcademicAuthorizationService`; Waka Akademik and Super Admin are authorized, while Wali Kelas is not granted institution-wide access.

## Preserved

- No dashboard/controller/route integration was added.
- Student attendance, teacher attendance, substitution, correction, alert/calendar, KPI, schedule, and session-generation behavior are unchanged.
- `session_source = RESCHEDULED` remains eligible when the raw session is an active replacement; only raw `session_status` controls cancellation/rescheduled exclusion.

## Data protection

No migration, schema change, seed/import, RBAC change, schedule data change, historical rewrite, or real-database write. Tests use only isolated test fixtures.
