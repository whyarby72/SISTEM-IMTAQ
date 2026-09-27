# Change Manifest

- **Change ID:** IMP-WAKA-UI-LOGIN
- **Task ID:** IMP-WAKA-UI-LOGIN
- **Title:** Menetralkan label halaman login
- **Date:** 2026-09-08
- **Owner module/workstream:** Shared local authentication presentation
- **Change class:** `MODULE_INTERNAL`
- **Business outcome:** Halaman login tidak lagi memberi kesan hanya untuk Admin Akademik; istilah berlaku untuk Super Admin, Waka, dan Wali Kelas.
- **Git branch:** Current local branch
- **Git commit / release:** Not created in this checkpoint

## Impact
- **Affected modules/workstreams:** Login presentation only
- **Source-of-truth entities/services affected:** None
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** NO CHANGE
- **Database migration:** NONE
- **Backward-compatibility impact:** None
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** `codex/CHANGE_IMPACTS/IMP-WAKA-UI-LOGIN-2026-09-08.md`, this manifest
- **Files modified:** `application/web/resources/views/auth/login.blade.php`, `codex/WORK_LOG.md`
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** `php artisan test tests/Feature/Auth/LocalAuthenticationTest.php`; `php artisan view:cache`
- **Regression scope:** Login availability, login/redirect behavior, invalid credentials, logout
- **Result:** PASS — 5 tests, 23 assertions
- **Staging result:** NOT_APPLICABLE — local UI checkpoint
- **Smoke test:** Super Admin and Waka login redirected to `/admin/academic`; Wali Kelas redirected to `/academic/dashboard` with `Pengisian Kehadiran`; Wali access to `/admin/academic` returned 403. Login label reads `Akses Sistem IMTAQ`.

## Deployment
- **Deploy readiness:** NOT_READY — local-only checkpoint
- **Deployment notes:** Presentation-only change; no migration or seed required.
- **Rollback / disable procedure:** Revert the login label replacement.
- **Database recovery dependency:** NONE

## Closeout
- **Known limitations/issues:** Role names in test fixtures remain historical fixture values and are not user-facing labels.
- **Documentation updated:** Change impact, manifest, and work log
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
