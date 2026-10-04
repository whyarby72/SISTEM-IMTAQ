# User & Akses controlled PILOT migration E1

**Task:** `SUPER-ADMIN-USER-ACCESS-CONTROLLED-PILOT-MIGRATION-E1`  
**Type:** `CONTROLLED_PILOT_SCHEMA_AND_AUTHORITY_BOOTSTRAP`  
**Execution date:** 2026-10-04, Asia/Jakarta  
**Decision:** `CONTROLLED_PILOT_MIGRATION_COMPLETED`

## Authorization and executable authority

- Owner/ChatGPT authorization: accepted R2 readiness decision
  `READY_FOR_CONTROLLED_PILOT_MIGRATION`.
- Repository: `whyarby72/SISTEM-IMTAQ`
- Branch: `feat/super-admin-user-access-preferences`
- Tested executable HEAD: `a7fbcead149086e331cb5e7e22ed161a892f8a4b`
- Exact CI: [37012461730](https://github.com/whyarby72/SISTEM-IMTAQ/actions/runs/37012461730) = SUCCESS.
- Entry worktree contained only the explicitly allowed uncommitted R2 readiness
  review; no tracked executable change was present.

## Fresh target preflight

The established local PILOT connection was inspected inside
`BEGIN TRANSACTION READ ONLY` and closed with `ROLLBACK`.

| Check | Result |
|---|---|
| Database | `imtaq` |
| PostgreSQL | `18.6 (Homebrew)` |
| `transaction_read_only` | `on` |
| Applied migrations | 43 |
| Pending migrations | exactly the three User & Akses migrations |
| Users | 9 |
| Effective SUPER_ADMIN | 1 |
| Expected role codes present | 3/3 |
| Partial/conflicting User & Akses schema | none |
| Other active database sessions | 0 |
| Blocking sessions / long transactions | 0 / 0 |
| Local Laravel queue/scheduler workers | none observed |
| Public Academic AI | OFF |

## Backup verification

- Database: `imtaq`
- Format: PostgreSQL custom format
- Path: `/Users/afradadmedia/DATA/PTAFRADAD/CHATGPTLOCAL/SISTEM-IMTAQ-PILOT-BACKUPS/imtaq-user-access-e1-20261004-085013.dump`
- Size: `1,051,065` bytes
- File mode: `0600`
- `pg_restore --list`: PASS
- Backup bytes are outside Git and were not inspected or copied into evidence.

## Migration result

Guarded command:

```text
php artisan migrate:guarded imtaq --connection=pgsql --host=127.0.0.1 --port=5432
```

Target guard passed and exactly these migrations completed:

1. `2026_10_02_000001_add_status_to_users_table`
2. `2026_10_02_000002_create_user_access_preference_tables`
3. `2026_10_02_000003_add_must_change_password_to_users_table`

Migration count changed `43 → 46`. Post-migration read-only verification found
`users.status` non-null with default `ACTIVE`, `users.must_change_password`
non-null with default `false`, and all three new tables present. User count
remained 9.

## Bootstrap result

After an independent target identity check, only this seeder ran:

```text
php artisan db:seed --database=pgsql --class=UserAccessFeatureSeeder --force
```

Result: PASS. Default `DatabaseSeeder` and unrelated seeders were not run.

### Permission and grant delta

- Three permission rows created, exactly one each:
  `academic.domain.manage`, `platform.institution.manage`,
  `platform.user.manage`.
- Four role-permission links added:
  - SUPER_ADMIN: all three
  - WAKA_AKADEMIK: `academic.domain.manage` only
  - WALI_KELAS: none
- No broad/manual grant or override was used.

### Feature registry

- 13 expected feature rows present.
- Required-permission mappings exactly match the accepted source contract.
- Non-null required permissions missing from the permission catalog: 0.
- `user_feature_overrides`: 0.
- `user_preferences`: 0.

## Existing-account safety

| Check | Before | After |
|---|---:|---:|
| Users | 9 | 9 |
| Effective role assignments | 6 | 6 |
| UserStaffLink rows | 7 | 7 |
| Users with `status=ACTIVE` | column absent | 9 |
| Users with `must_change_password=false` | column absent | 9 |

No user creation, account disable, password reset, role-assignment deletion,
Staff-link deletion, or user-feature/preference data was produced.

## Effective authorization postflight

Application services were invoked inside read-only transactions without HTTP
session writes or workaround overrides.

| Role | Academic students | User & Akses | System Settings | Academic full | Institution-wide |
|---|---|---|---|---|---|
| SUPER_ADMIN | allowed | allowed | allowed | true | true |
| WAKA_AKADEMIK | allowed | denied | denied | true | false |
| WALI_KELAS | denied | denied | denied | false | false |

This preserves Waka Academic authority and prevents Wali/platform escalation.
Scoped Wali dashboard/attendance behavior remains governed by its existing
business authorization and was not mutated or exercised here.

## Independent read-only postflight

A fresh process/session began `BEGIN TRANSACTION READ ONLY`, proved database
`imtaq`, PostgreSQL 18.6, and `transaction_read_only=on`, then independently
verified:

- migration count 46, target migrations 3, pending source migrations 0;
- users 9, ACTIVE 9, must-change-password false 9;
- required permissions 3;
- grant counts SUPER_ADMIN 3 / WAKA 1 / WALI 0;
- feature rows 13 and orphan required permissions 0;
- overrides/preferences 0/0;
- exact effective-access matrix above;
- Public Academic AI OFF.

The transaction ended with `ROLLBACK`.

## Database write summary

Authorized PILOT writes were limited to:

- three migration records and their additive User & Akses schema changes;
- three permission rows;
- four role-permission links;
- thirteen feature-registry rows.

No Academic attendance, student, schedule, roster, occurrence,
teacher-participation, correction, lock, AI/provider, or deployment state was
written. Application executable source changed: NO.

## Rollback/forward-fix disposition

No failure path was triggered. The verified backup is retained outside Git.
If a later defect is found after real User & Akses transactions exist,
destructive rollback is not the default; preserve history and use a separately
reviewed forward correction unless explicit restore authority is issued.

## Preserved boundaries

- `ACADEMIC-WALI-PILOT-FIRST-DAY-CONTROLLED-ATTENDANCE-UAT = HOLD / HUMAN_OBSERVATION_REQUIRED`
- Public Academic AI = OFF
- `IMP-S12-007 = NOT_STARTED`
- Canonical queue marker `SOC-MD-06` unchanged

## Closeout

```text
CONTROLLED_PILOT_MIGRATION = COMPLETED
POSTFLIGHT = PASS
PILOT_DATABASE = imtaq
MIGRATIONS_APPLIED = 3
BOOTSTRAP_SEEDER = UserAccessFeatureSeeder / PASS
USER_COUNT = 9 -> 9
PUBLIC_ACADEMIC_AI = OFF
ACADEMIC_BUSINESS_WRITE = NONE
APPLICATION_SOURCE_CHANGED = NO
BLOCKERS = NONE
NEXT_ATOMIC_TASK = RETURN_TO_CHATGPT_FOR_USER_ACCESS_CONTROLLED_PILOT_MIGRATION_E1_AUDIT
SAFE_TO_CLOSE = YES
```
