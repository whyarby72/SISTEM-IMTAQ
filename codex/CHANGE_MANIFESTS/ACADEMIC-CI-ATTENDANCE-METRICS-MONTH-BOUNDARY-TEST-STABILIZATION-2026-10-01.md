# Change Manifest — Academic CI Attendance Metrics Month Boundary Test Stabilization

**Task:** `ACADEMIC-CI-ATTENDANCE-METRICS-MONTH-BOUNDARY-TEST-STABILIZATION`  
**Type:** `TARGETED_TEST_RELIABILITY_REMEDIATION`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Implementation commit:** `734d849e1bafd13f44a1a33d136e2ef9ce79a3c8`  
**Exact CI:** `36788275716` — SUCCESS

## Root cause confirmed

`AttendanceSemanticMetricsServiceTest` derived both its fixture timestamps and
its queried period from the wall clock. The first test placed sessions at
`now()->subDays(2)` and `now()->subDay()` while querying
`now()->startOfMonth()` through `now()->endOfMonth()`. When the runtime crossed
from 30 September Asia/Jakarta to 1 October, the sessions remained in
September while the query period moved to October, producing
`eligible_opportunities = 0` instead of `1`.

## Change

Only `application/web/tests/Feature/Academic/AttendanceSemanticMetricsServiceTest.php`
was changed. The test class now freezes Carbon at
`2026-09-15 12:00:00 Asia/Jakarta` in `setUp()` and clears the test clock in
`tearDown()`. Production attendance services, denominator semantics,
occurrence semantics, migrations, schema, configuration, and pilot data were
not changed.

## Verification

| Check | Result |
|---|---|
| PHP lint | PASS |
| `git diff --check` | PASS |
| Local focused test | Guarded: local environment rejected by disposable database identity guard; no pilot access occurred |
| Disposable PostgreSQL focused/foundation verification | PASS |
| Migration-from-zero | PASS |
| PostgreSQL identity/extension checks | PASS |
| Foundation verification | PASS |
| GitHub Actions | Run `36788275716` SUCCESS on exact implementation commit |

## Safety boundary

| Field | Result |
|---|---|
| Pilot database access/write | NONE |
| K1/K2A/K2B/K3A/K3B provisioning | UNCHANGED; K2B NOT EXECUTED |
| Application production source | UNCHANGED |
| Migration/schema/runtime config | UNCHANGED |
| Public Academic AI | OFF |
| `IMP-S12-007` | `NOT_STARTED` |
| `SOC-MD-06` | PRESERVED |

## Routing

After the exact-current CI success, route back to
`ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`.
K2B still requires its own mandatory read-only preflight before any future
authorized write.
