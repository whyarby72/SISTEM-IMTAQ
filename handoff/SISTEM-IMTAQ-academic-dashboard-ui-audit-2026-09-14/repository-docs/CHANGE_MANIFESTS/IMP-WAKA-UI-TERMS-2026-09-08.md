# Change Manifest

- **Change ID:** IMP-WAKA-UI-TERMS
- **Task ID:** IMP-WAKA-UI-TERMS
- **Title:** Menyamakan istilah Waka Akademik pada halaman akademik
- **Date:** 2026-09-08
- **Owner module/workstream:** Academic admin presentation
- **Change class:** `MODULE_INTERNAL`
- **Business outcome:** Identitas pengguna dan istilah pengelola akademik konsisten di seluruh halaman akademik utama.
- **Git branch:** Current local branch
- **Git commit / release:** Not created in this checkpoint

## Impact
- **Affected modules/workstreams:** Academic admin views only
- **Source-of-truth entities/services affected:** None
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** NO CHANGE
- **Database migration:** NONE
- **Backward-compatibility impact:** None
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** `codex/CHANGE_IMPACTS/IMP-WAKA-UI-TERMS-2026-09-08.md`, this manifest
- **Files modified:** Five academic index Blade views, `application/web/resources/views/admin/academic/structure/index.blade.php`, `codex/WORK_LOG.md`
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** Admin academic feature tests; `php artisan view:cache`
- **Regression scope:** Rendering and access paths for Admin academic pages
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE — local UI checkpoint
- **Smoke test:** Browser visual audit found and corrected the remaining `Dashboard Admin` wording; label search confirms no `Admin Akademik` or `Dashboard Admin` remains in the audited academic/login views.

## Deployment
- **Deploy readiness:** NOT_READY — local-only checkpoint
- **Deployment notes:** Presentation-only change; no migration or seed required.
- **Rollback / disable procedure:** Revert the five view label replacements.
- **Database recovery dependency:** NONE

## Closeout
- **Known limitations/issues:** Login and non-academic legacy screens were not changed; they are outside this terminology checkpoint.
- **Documentation updated:** Change impact, manifest, and work log
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
