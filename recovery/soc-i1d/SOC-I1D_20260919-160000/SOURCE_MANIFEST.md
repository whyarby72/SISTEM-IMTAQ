# SOC-I1D Recovery Source Manifest

- `academic.php` — cutover timestamp configuration, default null
- `SessionOccurrenceCutover.php` — single feature/cutover/timezone/regime authority
- `SessionOccurrenceFeatureGate.php` — gated workflow adapter
- `CanonicalAttendanceSemanticService.php` — single denominator population owner and missing-occurrence DQ output
- `StudentAttendanceController.php` — canonical workflow cutover guard
- `show.blade.php` — pre-cutover legacy vs post-cutover canonical presentation guard
- `SessionOccurrenceCutoverTest.php` — gate and exact-boundary tests
- `AttendanceSemanticMetricsServiceTest.php` — post-cutover population, partial HELD, and no legacy fallback test

No migration or schema file was added.
