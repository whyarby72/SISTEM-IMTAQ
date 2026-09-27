# SOC-I1C Recovery Source Manifest

Post-change source copies are under `source/`.

- `academic.php` — feature gate configuration
- `SessionOccurrenceFeatureGate.php` — single gate authority
- `SessionOccurrenceAuthorizationService.php` — Wali/Waka scope checks
- `SessionOccurrenceWorkflowService.php` — transactional cancellation/reschedule integration
- `CanonicalSessionOccurrenceService.php` — existing SOC-I1B write primitive
- `SubstitutionService.php` — scoped Wali substitution compatibility path
- `StudentAttendanceController.php` — gated occurrence endpoints and correction flow
- `web.php` — occurrence routes
- `show.blade.php` — gated occurrence display/history/forms
- `StudentAttendanceUiTest.php` — gate and joint-scope regressions

No database schema file or migration was added.

