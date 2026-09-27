# Change Manifest

- **Change ID:** IMP-WAKA-KPI
- **Task ID:** IMP-WAKA-KPI
- **Title:** Penyelarasan KPI Dashboard Waka dengan data resmi Juli 2026
- **Date:** 2026-09-08
- **Owner module/workstream:** Academic admin dashboard
- **Change class:** `MODULE_INTERNAL`
- **Business outcome:** KPI Waka membedakan data resmi akademik dari data pilot dan menunjukkan basis 84 santri.
- **Git branch:** Local workspace
- **Git commit / release:** Not available in current workspace

## Impact
- **Affected modules/workstreams:** Admin dashboard KPI queries and presentation
- **Source-of-truth entities/services affected:** Official classes/teaching assignments and July snapshots
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None
- **Database migration:** NONE
- **Backward-compatibility impact:** None
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** This impact record and manifest
- **Files modified:** `application/web/app/Http/Controllers/Admin/AdminDashboardController.php`, `application/web/resources/views/admin/dashboard.blade.php`, `application/web/tests/Feature/Admin/AdminDashboardTest.php`, `codex/WORK_LOG.md`
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** `php artisan test tests/Feature/Admin/AdminDashboardTest.php`
- **Regression scope:** Waka/Super Admin dashboard access and KPI rendering
- **Result:** PASS — 2 tests, 6 assertions
- **Additional check:** `php artisan view:cache` PASS; browser Waka smoke shows 5 official classes, 84 santri, 16 academic teachers, and 2 approved schedules

## Closeout
- **Known limitations/issues:** Kehadiran hadir tetap merupakan hitungan transaksi operasional, bukan total hadir snapshot Juli. KPI jadwal mempertahankan seluruh jadwal berstatus disetujui karena sebagian data jadwal historis belum memiliki relasi assignment lengkap.
- **Documentation updated:** Work log and change records
- **Status:** DONE
