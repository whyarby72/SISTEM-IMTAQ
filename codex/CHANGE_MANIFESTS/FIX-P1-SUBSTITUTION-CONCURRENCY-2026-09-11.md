# Change Manifest

- **Change ID:** FIX-P1-SUBSTITUTION-CONCURRENCY-2026-09-11
- **Task ID:** P1 Phase 4 — Substitution Conflict & Concurrency
- **Date:** 2026-09-11
- **Status:** DONE
- **Git branch/commit:** Not available; repository has no Git metadata

## Files

- **Modified:** `application/web/app/Domains/Academic/Services/SubstitutionService.php`; `application/web/tests/Feature/Academic/SubstitutionServiceTest.php`; `codex/WORK_LOG.md`
- **Added:** `codex/CHANGE_IMPACTS/FIX-P1-SUBSTITUTION-CONCURRENCY-2026-09-11.md`; this manifest
- **Deleted:** None

## Authorization and state

Phase 2 `AcademicAuthorizationService` remains the authority boundary. Waka and Super Admin are allowed only with effective configured authority; service authorization does not bypass target resource state. Existing nullable actor calls remain compatible for internal legacy callers, while HTTP administration supplies the authenticated actor id.

## Conflict and transaction behavior

- Preflight conflict check remains for fast feedback.
- Transactional final check locks/reloads target `ClassSession`, rechecks writable state, rechecks full Academic authority, takes the candidate-teacher PostgreSQL advisory transaction lock, and queries authoritative conflicts again.
- Conflict sources are both candidate PRIMARY teaching assignments and candidate EXPECTED SUBSTITUTE participations.
- Overlap uses `[start, end)` semantics: `requested_start < other_end AND requested_end > other_start`.
- CANCELLED and RESCHEDULED sessions are excluded by existing active-state semantics; COMPLETED remains an active historical obligation only when its interval overlaps.
- On conflict or invalid target, no ScheduleChange, SUBSTITUTE participation, PRIMARY mutation, or success audit is written.
- PRIMARY participation and original teaching assignment remain unchanged.

## Lock and database audit

Lock order is `ClassSession → candidate teacher serialization resource → ScheduleChange/SessionTeacherParticipation writes`. The existing PostgreSQL `class_sessions_active_no_overlap` exclusion constraint covers class/time only, not candidate teacher substitution overlap; advisory locking closes that separate candidate domain without schema redesign.

## Verification

- Targeted: **39 tests / 219 assertions / 0 failures**
- Full `php artisan test --compact tests/Feature/Academic`: **207 tests / 803 assertions / 0 failures**
- `php artisan view:cache`: PASS
- PHP lint/Pint: PASS
- Real PostgreSQL concurrent substitution test: **DEFERRED_TO_STAGING** because local tests use SQLite in-memory.
- Migration: NONE
- Seed execution: NONE
- Historical data rewrite: NO
- P0 regression: PASS through full Academic suite

## Explicitly deferred

Correction review concurrency, locked correction redesign, controlled-vocabulary constraints, UI/KPI/schedule redesign, and P1 Phase 5.
