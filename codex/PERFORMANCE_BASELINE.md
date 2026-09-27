# Performance Baseline — Initial Local Academic Suite

- Date: 2026-09-04
- Environment: local Laravel test environment in `application/web/`
- Command: `php artisan test tests/Feature/Academic`
- Result: 127 tests, 404 assertions — PASS
- Observed duration: 965 ms

This is a repeatable regression-runtime baseline, not a production capacity target. It does not represent concurrent users, realistic data volume, network latency, or deployed infrastructure. Representative route/query timing or an agreed load harness remains a follow-up checkpoint.

## Authenticated dashboard route fixture

- Command: `/usr/bin/time -p php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Fixture: authorized Wali Kelas, two classes, one session per class
- Result: 8 tests, 18 assertions — PASS
- Observed wall time: 0.81 s (`real`); PHPUnit application duration: 271 ms

The route assertion verifies the authenticated `/academic/dashboard` path and rendered view. It is still a small test fixture and must not be interpreted as production throughput.

## Dashboard query-count guardrail

- Fixture: authorized Waka Akademik, two classes, one session per class
- Measurement: database query listener during `AcademicRoleDashboardService::forUser`
- Guardrail: no more than 20 queries for the fixture
- Result: 9 tests, 20 assertions — PASS; query count stayed within the guardrail

This guardrail is intended to catch an unexpected query-count regression; it is not a universal performance target.

## Configurable concurrent-request harness

- Utility: `scripts/benchmark_academic_dashboard.php`
- Inputs: explicit local URL, request count (maximum 200), concurrency (maximum 20)
- Outputs: completed requests, errors, HTTP statuses, wall time, and min/p95/max latency
- Safety: no default URL, no production target, bounded inputs, no writes
- Validation: PHP syntax and `--help` — PASS
- Live run: deferred until a local server and authenticated test session are explicitly available
