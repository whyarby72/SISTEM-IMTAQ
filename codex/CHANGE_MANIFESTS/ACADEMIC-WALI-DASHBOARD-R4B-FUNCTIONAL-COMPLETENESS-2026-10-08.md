# Change Manifest — Academic Wali Dashboard R4B

## Identity

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WALI-DASHBOARD-R4B-FUNCTIONAL-COMPLETENESS`
- Branch: `feat/super-admin-user-access-preferences`
- Required entry HEAD: `57779f099b19c09ece69515681b8ddcb973a9228`
- Entry branch and remote: verified identical before implementation
- Date: `2026-10-08` Asia/Jakarta

## Change purpose

Remove the silent `limit(12)` from the Wali selected-period attendance-session
read model. Add server-owned full-dataset summary counts, overdue/today
`needs_action` classification, a safe dashboard filter, and a CTA to expose
authorized work that was previously hidden by the truncated collection or by a
today-only urgency calculation.

## Exact write scope

### Application files

- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Http/Controllers/Academic/AcademicDashboardController.php`
- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`

### Governance/evidence files

- `codex/TASK_CONTEXTS/ACADEMIC-WALI-DASHBOARD-R4B-FUNCTIONAL-COMPLETENESS.md`
- this manifest
- final state/routing/evidence files only as required at closeout

Pre-existing `codex/AUDITS/` files are preserved and not included.

## Functional evidence

- Removed the Wali period-session semantic cap.
- Summary totals and status filters are derived from the complete server-side
  collection, not from the filtered display subset.
- `needs_action` covers overdue and today actionable period sessions and
  excludes future sessions.
- Occurrence-pending and missing expected teacher attendance are represented in
  the action summary.
- Existing point-in-time/joint-session authorization remains delegated to the
  existing scope services.
- Empty-period summary is explicit and zero-valued.

## Safety and non-scope

- Database access/write: `NONE / NONE` (PILOT not accessed).
- Migrations/schema: unchanged.
- Dependencies/runtime configuration: unchanged.
- Academic business data: unchanged.
- AI/provider/Public Academic AI: unchanged; Public Academic AI remains `OFF`.
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`.
- Human UAT: deferred.
- `SOC-MD-06`: unchanged.

## Validation

- PHP lint: PASS.
- Pint changed-file check: PASS.
- Composer validate: PASS.
- Blade view cache: PASS.
- Route listing: PASS.
- Project structure guard: PASS.
- `git diff --check`: PASS.
- Focused local tests: `BLOCKED_SAFE`; the existing database identity guard
  rejected the protected local PILOT identity before any business query/write.
- Disposable PostgreSQL GitHub Actions verification: PENDING at manifest
  creation; exact run and result must be recorded before final closeout.

## Recovery

Revert the R4B application/test commit and associated evidence commit(s) if the
exact CI or ChatGPT audit rejects the change. No migration rollback or database
restore is required because no database was changed.

## Status

Implementation status: `PENDING_EXACT_CI`
Candidate closeout: `R4B_P1=READY_FOR_CHATGPT_AUDIT` only after exact CI success.
