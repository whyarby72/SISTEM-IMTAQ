# Phase 2R-B2C Pre-Implementation Recovery Provenance

- Task: Phase 2R-B2C — Academic Dashboard Export Canonical Metrics Consumer Migration
- Mode: pre-B2C recovery snapshot
- Created: 2026-09-18 10:57:40 Asia/Jakarta
- Repository root: `/Users/afradadmedia/DATA/PTAFRADAD/CHATGPTLOCAL/SISTEM-IMTAQ`
- Application root: `application/web`
- Implementation mode at snapshot: pending export audit; no application source mutation has been performed.
- CR-B2B-D source checkpoint: `recovery/cr-b2b-d/CR-B2B-D_20260918-102859`
- CR-B2B-D provenance SHA-256: `4798c6690ecd0d1aed369ea78d33e9c51aa44190e2e050f9367019849629f2b7`
- CR-B2B-D checksum manifest SHA-256: `336d0ac607c7da62a69684ec2e418463ec663c69651ff3c74e5ec41cb913e0c7`

## Captured artifacts

- `AcademicDashboardExportService.php.pre-b2c`: exact export service before B2C validation.
- `AcademicRoleDashboardService.php.pre-b2c`: current post-B2B canonical dashboard consumer.
- `AcademicRoleDashboardServiceTest.php.pre-b2c`: directly related export/dashboard regression test.
- `PRE_B2C_APPLICATION_SOURCE_MANIFEST.sha256`: source manifest carried forward from verified CR-B2B-D state.
- `CR-B2B-D_PROVENANCE.md`: upstream durable checkpoint provenance.

No credentials, `.env`, database dump, runtime cache, vendor directory, migration, seed, or imported data is included in this recovery snapshot.
