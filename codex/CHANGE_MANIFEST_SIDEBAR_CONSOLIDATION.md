# Change Manifest — Academic Sidebar Consolidation

- **Change ID:** IMP-ACADEMIC-SIDEBAR-CONSOLIDATION
- **Date:** 2026-09-09
- **Scope:** Academic Waka Dashboard navigation shell
- **Outcome:** Dashboard Waka menggunakan partial sidebar akademik yang sama dengan halaman laporan, review, kehadiran, dan jadwal.

## Safety

- **Database migration/seed/import:** NONE
- **Business logic:** NONE
- **Authorization:** Existing role-based menu visibility preserved and role resolved by the dashboard service when supplied.
- **Historical facts:** NONE changed.

## Files

- **Modified:** `application/web/resources/views/academic/dashboard.blade.php`; `application/web/resources/views/academic/partials/sidebar.blade.php`; `codex/WORK_LOG.md`
- **Added:** This manifest
- **Deleted:** None

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php --compact`: PASS, 19 tests / 65 assertions
- `php artisan view:cache`: PASS
- PHP lint for dashboard controller: PASS

**Status:** DONE
