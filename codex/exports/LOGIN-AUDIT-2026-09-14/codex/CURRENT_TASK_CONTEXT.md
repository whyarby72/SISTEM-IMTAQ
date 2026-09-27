# CURRENT TASK CONTEXT

**Task:** `IMP-ADMIN-ROOT-002`  
**State:** `COMPLETE`
**Goal:** Memastikan Super Admin dapat mengakses seluruh dashboard akademik.

This file is the minimum-context entry point. Detailed authority remains in linked contracts.

## REQUIRED NOW
Read only:
1. `docs/05_migration/MIGRATION_CONTRACT.md`
2. `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md` (migration/change gate sections)
3. `modules/CHANGE_IMPACT_RULES.md`
4. `templates/CHANGE_IMPACT_TEMPLATE.md`
5. `templates/CHANGE_MANIFEST_TEMPLATE.md`

## Current write scope
- `codex/JULY_2026_MAPPING_REVIEW.md`
- `codex/CHANGE_IMPACTS/IMP-ADM-007-2026-09-04.md`
- `codex/CHANGE_MANIFESTS/IMP-ADM-007-2026-09-04.md`
- task metadata and work log for this atomic checkpoint

Previous task `IMP-ADM-006` added the Admin structure UI. This checkpoint entered and verified the target master data; no historical import has run.

## Resume point

Publication approval is complete; Super Admin local access and all-class Academic dashboard scope are verified. Do not create session-level or per-student attendance facts.

## Previous write scope
- `application/web/app/Http/Controllers/Admin/StaffController.php`
- `application/web/resources/views/admin/academic/staff/edit.blade.php`
- `application/web/resources/views/admin/academic/staff/index.blade.php`
- `application/web/routes/web.php`
- `application/web/tests/Feature/Admin/StaffAdminTest.php`
- `codex/CHANGE_IMPACTS/IMP-ADM-004-2026-09-04.md`
- `codex/CHANGE_MANIFESTS/IMP-ADM-004-2026-09-04.md`
- task metadata for this atomic checkpoint

Previous task `IMP-ADM-003` completed historical-safe class editing.

## Previous write scope
- `application/web/app/Http/Controllers/Admin/AcademicClassController.php`
- `application/web/resources/views/admin/academic/classes/edit.blade.php`
- `application/web/resources/views/admin/academic/classes/index.blade.php`
- `application/web/routes/web.php`
- `application/web/tests/Feature/Admin/AcademicClassAdminTest.php`
- `codex/CHANGE_IMPACTS/IMP-ADM-003-2026-09-04.md`
- `codex/CHANGE_MANIFESTS/IMP-ADM-003-2026-09-04.md`
- task metadata for this atomic checkpoint

Previous task `IMP-ADM-002` completed the unified Academic Admin landing navigation.

## Previous write scope
- `application/web/app/Http/Controllers/Admin/AdminDashboardController.php`
- `application/web/resources/views/admin/dashboard.blade.php`
- `application/web/routes/web.php`
- `application/web/tests/Feature/Admin/AdminDashboardTest.php`
- `codex/CHANGE_IMPACTS/IMP-ADM-002-2026-09-04.md`
- `codex/CHANGE_MANIFESTS/IMP-ADM-002-2026-09-04.md`
- task metadata for this atomic checkpoint

Previous admin task `IMP-ADM-001` completed class, staff, and schedule list/create. This checkpoint adds the unified admin landing navigation.

## Previous write scope
- `application/web/app/Http/Controllers/Admin/AcademicClassController.php`
- `application/web/app/Http/Controllers/Admin/StaffController.php`
- `application/web/resources/views/admin/academic/classes/`
- `application/web/resources/views/admin/academic/staff/`
- `application/web/routes/web.php`
- `application/web/tests/Feature/Admin/AcademicClassAdminTest.php`
- `codex/CHANGE_IMPACTS/IMP-ADM-001-2026-09-04.md`
- `codex/CHANGE_MANIFESTS/IMP-ADM-001-2026-09-04.md`
- task metadata for this atomic checkpoint

Previous pilot task `IMP-S12-006` and admin tasks through `IMP-ADM-004` are complete. This checkpoint adds historical-safe schedule editing; delete remains excluded.

## Stop/escalate triggers
Do not use report-card versions as the transcript source or mutate published transcript versions. Do not infer an owner without an effective Wali Kelas assignment and linked authorized identity.

## Task completion source
`codex/TASK_QUEUE.md` remains the queue authority, but it does **not** need to be re-read at every checkpoint unless task status/dependency changes.
