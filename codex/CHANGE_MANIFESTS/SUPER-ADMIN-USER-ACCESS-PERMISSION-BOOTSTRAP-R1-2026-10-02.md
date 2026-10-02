# Change Manifest — SUPER-ADMIN-USER-ACCESS-PERMISSION-BOOTSTRAP-R1

**Project:** SISTEM-IMTAQ
**Task:** `SUPER-ADMIN-USER-ACCESS-PERMISSION-BOOTSTRAP-R1`
**Date:** 2026-10-02
**Scope:** atomic source remediation and disposable-test evidence only

## Result

Implementation is complete locally, but the required disposable PostgreSQL
test cannot be claimed PASS from this workstation. The configured local
database target is the protected PILOT `imtaq`; the test guard correctly
rejects it. An explicit `imtaq_test_r1` attempt also could not connect because
no permitted disposable PostgreSQL endpoint is available in this environment.

Therefore the source result is **PASS_PENDING_DISPOSABLE_POSTGRES_CI** and the
overall R1 decision remains **NOT CLOSED** until disposable PostgreSQL CI
executes the focused and regression tests.

## Root cause addressed

`UserAccessFeatureSeeder` created only `platform.user.manage`, while its
feature definitions referenced `academic.domain.manage` and
`platform.institution.manage`. The resulting registry could not resolve all
required permissions against the legacy PILOT catalog.

## Permission contract

The seeder now idempotently ensures exactly these permission codes exist:

- `academic.domain.manage`
- `platform.institution.manage`
- `platform.user.manage`

Grant matrix:

| Role | academic.domain.manage | platform.institution.manage | platform.user.manage |
|---|---:|---:|---:|
| SUPER_ADMIN | yes | yes | yes |
| WAKA_AKADEMIK | yes | no | no |
| WALI_KELAS | no | no | no |

Existing unrelated permissions are preserved. No role permission is revoked
or deleted. No role is created by this seeder.

## Changed files

- `application/web/database/seeders/UserAccessFeatureSeeder.php`
- `application/web/tests/Feature/Admin/UserAccessManagementTest.php`
- this change manifest

Preceding read-only readiness governance files remain preserved and are not
part of the executable source scope.

## Verification

- Branch: `feat/super-admin-user-access-preferences`.
- Starting HEAD: `67672bdbf1dab18634c07517316780891debf467`.
- Local executable diff is limited to the seeder and focused test.
- PHP lint and diff checks are required before closeout.
- Focused test was first refused by the database guard when local config
  pointed to PILOT `imtaq`; no SQL write occurred.
- Retry with explicit disposable name `imtaq_test_r1` was blocked before test
  execution by unavailable local PostgreSQL connectivity. No fallback to
  SQLite was used.
- Disposable PostgreSQL CI must run: the focused seeder test, full
  `UserAccessManagementTest`, Academic authorization tests, and
  `scripts/verify-foundation.sh`.

## Safety boundaries

```text
PILOT_DATABASE_WRITE = NONE
PILOT_MIGRATION = NOT_RUN
PILOT_SEED = NOT_RUN
STAGING_PRODUCTION_ACCESS = NONE
AI_PROVIDER_MUTATION = NONE
PUBLIC_ACADEMIC_AI = OFF
ACADEMIC_FIRST_DAY_UAT = HOLD / HUMAN_OBSERVATION_REQUIRED
MIGRATION_CREATED = NO
```

No OpenAI call, provider activation, runtime change, attendance/schedule/
roster mutation, migration, or database write was performed.

## Acceptance still required

The disposable PostgreSQL run must prove:

1. clean bootstrap when all three permissions are initially absent;
2. exactly one permission row per required code;
3. exact grant matrix above;
4. every non-null feature required permission resolves to a Permission;
5. SUPER_ADMIN resolves the three representative feature gates;
6. two seeder runs create no duplicate feature or role-permission relation;
7. Public Academic AI remains disabled;
8. Academic/foundation regression remains green.

Only after those checks and exact CI evidence may this R1 be marked
`COMPLETED / PASS`. The preceding PILOT readiness decision remains HOLD until
that review is rerun against the corrected source and an authorized migration
plan.

**Next task:** rerun `SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-READINESS-REVIEW`.
