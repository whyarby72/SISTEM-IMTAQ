# Change Manifest — Academic Wali Dashboard R4A2-1

## Identity

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WALI-DASHBOARD-R4A2-1-SESSION-DATE-PARTITION-AND-NEXT-SESSION-ENTITLEMENT`
- Starting executable HEAD: `4043e686685a468c6d61a2038a6cd891c1eb4195`
- Branch: `feat/super-admin-user-access-preferences`
- Scope: targeted Academic Wali dashboard read-model correction only

## Problem and corrective design

- Joint-session partitioning now derives the canonical session class scope from `AcademicClassScopeResolver`, intersects it with the Wali entitlement windows at the session's Asia/Jakarta business date, and sends only the resulting class partition to the dashboard read model.
- `planned_start_at` is converted through `AcademicBusinessTime::date()` before the entitlement intersection. UTC storage is not used as a business-day proxy.
- A Wali therefore receives the same physical joint session when their class is a non-anchor scope-group class, while the participant collection and class label remain limited to that effective class.
- The operational next-session query keeps today's operational scope separate from a future entitlement scope. The future scope is bounded by the active class academic-year end and preserves end-exclusive assignment windows.
- The existing `limit(12)` on the period attendance-session list is unchanged.

## Changed files

- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Domains/Academic/Services/WaliClassEntitlementResolver.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-DASHBOARD-R4A2-1-SESSION-DATE-PARTITION-AND-NEXT-SESSION-ENTITLEMENT-2026-10-06.md`

No migration, schema, dependency, runtime configuration, deployment, or database files were changed.

## Test coverage added or preserved

- Non-anchor Wali access to a joint session is covered with a single effective participant partition and class label.
- A next session on the first date after `effective_until` is excluded.
- A next session during a future A→B homeroom transition resolves to the future class partition and label.
- Existing anchor Wali, Waka full-authority, ordinary-session, trend, period-counter, future-only-role, and read-only dashboard tests remain unchanged and are retained.
- No finalizer, correction, lock, schedule/session generation, roster, or teacher-participation code was changed.

## Validation status

- PHP lint: PASS for changed PHP files.
- Pint: PASS.
- `git diff --check`: PASS.
- `composer validate --strict`: PASS.
- `php artisan view:cache`: PASS.
- `php artisan route:list --except-vendor`: PASS.
- `python3 scripts/check_project_structure.py`: PASS.
- Local focused PHPUnit: BLOCKED safely by the existing fail-closed `TestDatabaseIdentityGuard` because local configuration resolves to the protected pilot identity; no database query or write was allowed.
- Disposable PostgreSQL focused/full regression and exact GitHub Actions verification: PENDING at manifest creation.

## Safety boundary

- PILOT access/write: `NONE / NONE`.
- Database write: `NONE`.
- Historical business-data rewrite: `NONE`.
- AI/provider mutation: `NONE`.
- Public Academic AI: `OFF`.
- `IMP-S12-007`: `NOT_STARTED`.
- `SOC-MD-06`: preserved.
- Human UAT: deferred; this manifest does not self-authorize controlled pilot use.

## Decision gate

The implementation decision is not promoted until the disposable PostgreSQL regression and exact current-head GitHub Actions run are green. Expected success decision: `ACADEMIC_WALI_R4A2_1_IMPLEMENTED_PASS`. If future transition behavior cannot be safely evidenced while P1 is fixed, use `ACADEMIC_WALI_R4A2_1_PARTIAL / NEXT_SESSION_TRANSITION_UX_DECISION_REQUIRED`; if cross-class leakage remains, use `ACADEMIC_WALI_R4A2_1_HOLD`.

