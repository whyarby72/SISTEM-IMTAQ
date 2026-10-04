# User & Akses — PILOT migration readiness recheck R2

**Task:** `SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-READINESS-RECHECK-R2-LOCAL`  
**Inspection date:** 2026-10-04, Asia/Jakarta  
**Review status:** `COMPLETED`  
**Readiness decision:** **READY_FOR_CONTROLLED_PILOT_MIGRATION**  
**PILOT database write:** `NONE`

## Executive decision

The previous source blocker is resolved in R1/R1R. The exact branch and final
CI are valid, the PILOT is at a clean pre-rollout state with all three User &
Akses migrations pending, an effective Super Admin exists, and no partial
schema was found. The source seeder now guarantees the required permission
catalog and role grant matrix idempotently, while the existing feature resolver
continues to require both feature state and permission/scope. The target is
therefore ready for a separately authorized controlled migration.

This is a readiness decision only. It does not authorize migration, seeding,
login/UAT, permission repair, or any Academic/PILOT business-data operation.

## 1. Git and CI authority

- Repository: `whyarby72/SISTEM-IMTAQ`
- Branch: `feat/super-admin-user-access-preferences`
- Starting/final inspected HEAD: `a7fbcead149086e331cb5e7e22ed161a892f8a4b`
- Worktree: clean; local and `origin/feat/super-admin-user-access-preferences` match.
- Exact GitHub Actions run: [37012461730](https://github.com/whyarby72/SISTEM-IMTAQ/actions/runs/37012461730) = `SUCCESS` on the exact HEAD.
- Foundation result: PostgreSQL 18.6 disposable service, migration replay from zero, schema assertions, 15 passed, 536 warnings, 2267 assertions, 0 failed.

## 2. PILOT identity and read-only method

Laravel resolved the established local PostgreSQL connection. Inspection used
one explicit transaction boundary:

```sql
BEGIN TRANSACTION READ ONLY;
SELECT current_database();
SHOW server_version;
SHOW transaction_read_only;
-- SELECT/SHOW inspection only
ROLLBACK;
```

Observed:

| Check | Result |
|---|---|
| Database | `imtaq` |
| PostgreSQL | `18.6 (Homebrew)` |
| `transaction_read_only` | `on` |
| Transaction close | `ROLLBACK` in the guarded command path |
| Database write | `NONE` |

No names, emails, password hashes, tokens, credentials, or personal
identifiers were selected or recorded.

## 3. Current migration/schema state

The exact target migrations were absent from the PILOT migration table:

1. `2026_10_02_000001_add_status_to_users_table`
2. `2026_10_02_000002_create_user_access_preference_tables`
3. `2026_10_02_000003_add_must_change_password_to_users_table`

Classification: `ALL_PENDING`. No partial User & Akses schema was present:

- `users.status`: absent
- `users.must_change_password`: absent
- `features`: absent

The source migration review confirms additive PostgreSQL-compatible changes,
ACTIVE/false defaults for existing users, RESTRICT foreign keys, unique
user-feature and user-preference keys, JSONB preferences, and reverse
dependency order `000003 → 000002 → 000001`.

## 4. PILOT account/role baseline

Aggregate-only read-only results:

| Metric | Count |
|---|---:|
| Users | 9 |
| Effective `SUPER_ADMIN` | 1 |
| Effective `WAKA_AKADEMIK` | 1 |
| Effective `WALI_KELAS` | 4 |
| UserStaffLink rows | 7 |
| Duplicate Staff links | 0 |
| Orphan user/staff links | 0 |
| Users without effective role | 3 |

Current role codes are `SUPER_ADMIN`, `WAKA_AKADEMIK`, and `WALI_KELAS`.
Current permission codes are the two legacy codes
`academic.dashboard.export` and `academic.student_attendance.handover_complete`.
Current role-permission counts are SUPER_ADMIN `0`, WAKA_AKADEMIK `2`, and
WALI_KELAS `0`. The one effective Super Admin satisfies the last-admin
authority precondition under the current legacy/default account contract.

## 5. R1/R1R source bootstrap result

`UserAccessFeatureSeeder` was inspected without execution. It uses
`firstOrCreate` for the three required permissions and
`syncWithoutDetaching` for role grants; feature rows use `updateOrCreate`.
The required catalog is:

- `academic.domain.manage`
- `platform.institution.manage`
- `platform.user.manage`

Projected grants:

| Role | Required result |
|---|---|
| `SUPER_ADMIN` | all three permissions |
| `WAKA_AKADEMIK` | `academic.domain.manage` only |
| `WALI_KELAS` | none of the three management permissions |

The R1 tests assert the catalog, grant matrix, required-permission coverage,
and repeated-seed idempotency. R1R aligned Academic dashboard fixtures so the
role records exist before bootstrap; exact CI proves the resulting regression
path. No seeder was executed against PILOT.

## 6. Pre/post authority compatibility

| Role | Pre-rollout legacy authority | Projected post-rollout authority |
|---|---|---|
| SUPER_ADMIN | existing institutional/Academic role authority | retains Academic master, System Settings, and gains User & Access through the three explicit grants |
| WAKA_AKADEMIK | existing Academic role authority | retains Academic domain management only; no institutional or platform-user grant |
| WALI_KELAS | scoped Academic workflow | no management/institutional/User & Access grant; existing scope checks remain authoritative |

The former blocker `USER_ACCESS_PILOT_BOOTSTRAP_AUTHORITY_COMPATIBILITY` is
`RESOLVED_IN_SOURCE / TARGET_EXECUTION_PENDING`. The current PILOT catalog
still lacks the new permission codes, as expected before migration/seed; this
is not an unresolved source blocker.

## 7. Feature registry and authorization contract

The source registry mapping is:

- no required permission: `academic.dashboard`, `academic.attendance`,
  `academic.attendance_review`, `academic.reports`
- `academic.domain.manage`: `academic.students`, `academic.classes`,
  `academic.structure`, `academic.schedules`, `academic.subjects`,
  `academic.staff`
- `platform.user.manage`: `platform.user_access`
- `platform.institution.manage`: `platform.system_settings`,
  `ai.academic_assistant`

`FeatureAccessResolver` requires the registered feature to be system-enabled,
not disabled by user override, permission-allowed, and scope-allowed. An
`ENABLED` override cannot bypass a missing permission. `EnsureFeatureAccess`
enforces this server-side. `AcademicAuthorizationService` remains composed
with the feature gate and retains the legacy fallback only while the new
authority permissions are not configured; the projected seeded state uses the
explicit permissions. Registry integrity and backend authorization are PASS
by source/tests; the registry is not yet present in PILOT because migration is
pending.

## 8. Existing-user compatibility and AI separation

The migrations assign `status=ACTIVE` and `must_change_password=false` to
existing rows. They do not create users, roles, assignments, overrides,
preferences, password resets, or sessions. Existing user count is 9 and the
projected defaults preserve current accounts.

Public Academic AI is `OFF` from `config('academic.ai.assistant_enabled')`.
The `ai.academic_assistant` registry entitlement does not activate provider
runtime, credentials, model calls, or the public controller gate.

`ACADEMIC-WALI-PILOT-FIRST-DAY-CONTROLLED-ATTENDANCE-UAT` remains
`HOLD / HUMAN_OBSERVATION_REQUIRED`; no attendance, roster, schedule, lock,
teacher-participation, or correction operation was performed.

## 9. Future controlled rollout sequence — not executed

1. Reconfirm exact branch/HEAD and clean worktree.
2. Confirm fresh protected PILOT backup and restore/recovery evidence.
3. Close application/queue writers and establish maintenance control.
4. Re-prove `imtaq`, PostgreSQL 18.x, `transaction_read_only` preflight,
   exact three pending migrations, stable account/role baseline, and locks.
5. Run only the guarded three migrations; never `migrate:fresh`.
6. Run only `UserAccessFeatureSeeder`; never default `DatabaseSeeder`.
7. Verify schema, 13 feature codes, three permission definitions, role grants,
   effective Super Admin/Waka access, Wali negative escalation, ACTIVE/false
   user defaults, Public Academic AI OFF, and append-only deployment evidence.
8. Open only after an independent fresh read-only postflight and evidence
   closeout pass.

## 10. Rollback/forward-fix disposition

- Migration failure: remain in maintenance; inspect applied subset and recover
  only through reviewed dependency-aware recovery, not blind rollback.
- Seed failure: do not reopen with partial registry; inventory and use a
  reviewed idempotent forward correction after authority resolution.
- Postflight authorization failure: keep closed; no broad manual grants or
  resolver bypass; use a reviewed narrow forward fix.
- Existing User & Access transactions: preserve history and prefer reviewed
  forward correction over destructive restore/drop operations.

## 11. Closeout

- `PILOT_DATABASE = imtaq`
- `TRANSACTION_READ_ONLY = ON`
- `PENDING_USER_ACCESS_MIGRATIONS = 3 / ALL_PENDING`
- `EFFECTIVE_SUPER_ADMIN = 1`
- `SOURCE_BOOTSTRAP = RESOLVED_IN_SOURCE / TARGET_EXECUTION_PENDING`
- `PUBLIC_ACADEMIC_AI = OFF`
- `PILOT_DATABASE_WRITE = NONE`
- `MIGRATION_EXECUTED = NO`
- `SEEDER_EXECUTED = NO`
- `APPLICATION_SOURCE_CHANGED = NO`
- `DECISION = READY_FOR_CONTROLLED_PILOT_MIGRATION`
- `NEXT_ATOMIC_TASK = RETURN_TO_CHATGPT_FOR_USER_ACCESS_PILOT_MIGRATION_READINESS_R2_AUDIT`
- `SAFE_TO_CLOSE = YES`

This review artifact is local and uncommitted, as required. No governance
state was rewritten and no migration execution was started.
