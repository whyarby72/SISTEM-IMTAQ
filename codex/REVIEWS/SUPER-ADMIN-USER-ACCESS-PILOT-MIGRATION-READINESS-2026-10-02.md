# User & Akses — PILOT migration readiness review

**Task:** `SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-READINESS-REVIEW`
**Type:** `READ_ONLY_PILOT_MIGRATION_READINESS_REVIEW`
**Inspection date:** 2026-10-02, Asia/Jakarta
**Review:** COMPLETED
**Readiness decision:** **HOLD**
**PILOT database write:** NONE

## Executive decision

The three migrations are additive and not yet applied; no partial schema was found. Existing-account defaults are compatible, and one effective institutional SUPER_ADMIN exists. However, **the current registry bootstrap would deny previously authorized Academic administration and System Settings access**. The PILOT has neither `academic.domain.manage` nor `platform.institution.manage`; `UserAccessFeatureSeeder` requires them on eight feature codes but only creates/grants `platform.user.manage`.

This is a concrete deployment compatibility blocker, not a reason to weaken the resolver, grant broad permissions automatically, or run migrations first. Accepted V1R CI is valid implementation evidence but does not prove compatibility with this live legacy permission catalog. No implementation, permission repair, migration, seeding, login, or HTTP business action was performed.

## 1. Repository and CI authority

- Repository: `whyarby72/SISTEM-IMTAQ` (the verified spelling).
- Branch: `feat/super-admin-user-access-preferences`.
- Starting and final Git HEAD: `67672bdbf1dab18634c07517316780891debf467`.
- Entry worktree: clean. `git ls-remote origin refs/heads/feat/super-admin-user-access-preferences` matched that HEAD.
- Tested executable HEAD: `465be64a6d5263def7d1eb3554e35590426346ec`.
- [Authoritative Actions run 36976686299](https://github.com/whyarby72/SISTEM-IMTAQ/actions/runs/36976686299): completed / SUCCESS; `gh run view` confirmed `headSha=465be64a6d5263def7d1eb3554e35590426346ec`.
- `git diff --name-status 465be64a6d5263def7d1eb3554e35590426346ec..67672bdbf1dab18634c07517316780891debf467` returned only `EVIDENCE_INDEX.json` and `PROJECT_STATE.json`.
- **No claim of exact-head CI on 67672bd.** Executable equivalence to the tested commit is established. This review leaves only uncommitted governance/evidence changes; no commit/push was requested by this review contract.

## 2. PILOT identity and read-only method

Laravel environment/configuration loaders were used only to resolve the local database settings and the boolean Public Academic AI flag, without booting HTTP/session/auth/provider workflows. Configuration guards required `pgsql`, `imtaq`, local host, and no alternate DB URL. Credentials were passed privately to PDO; no credential values were printed or recorded.

Two successful connections independently began with:

```sql
BEGIN TRANSACTION READ ONLY;
SELECT current_database(), inet_server_port(),
       current_setting('server_version'),
       current_setting('transaction_read_only');
-- All subsequent inspection statements were SELECT / SHOW.
ROLLBACK;
```

| Evidence | Observed |
|---|---|
| Laravel configured target | `127.0.0.1:5432/imtaq` |
| PostgreSQL actual database / port | `imtaq` / `5432` |
| PostgreSQL version | `18.6 (Homebrew)` |
| `transaction_read_only` | `on` in both transactions; also `on` at end of first |
| PostgreSQL Jakarta business date | `2026-10-02` |
| Transaction end | ROLLBACK, both connections |
| Database writes / DDL / seeding | NONE |

An initial sandbox connection attempt failed before any database query; the successful retry used the same read-only boundary with local-network permission. Exceptions were normalized to class/code, not raw messages. No names, emails, password hashes, tokens, student records, credential rows, or AI-provider rows were selected. Role effectiveness used the model's inclusive start / exclusive end date contract: `effective_from IS NULL OR effective_from <= Jakarta_date`, and `effective_until IS NULL OR effective_until > Jakarta_date`.

## 3. Current schema and migration applicability

| Item | PILOT observation |
|---|---|
| `users.status` | ABSENT |
| `users.must_change_password` | ABSENT |
| `features` | ABSENT |
| `user_feature_overrides` | ABSENT |
| `user_preferences` | ABSENT |
| `platform.user.manage` permission | ABSENT |
| Applied migrations | 43 |
| Applied migration names missing from source | 0 |
| Pending migrations | Exactly the following 3 |

1. `2026_10_02_000001_add_status_to_users_table`
2. `2026_10_02_000002_create_user_access_preference_tables`
3. `2026_10_02_000003_add_must_change_password_to_users_table`

`information_schema.columns`, `information_schema.tables`, `migrations`, and `pg_class` inspection found no partial application or relation-name collisions for the three new tables or `users_status_index`. `users.id` is PostgreSQL `bigint`, compatible with the new `foreignId` columns. Migration applicability is structurally PASS; execution remains HOLD for authorization compatibility.

## 4. Existing accounts and authority (aggregates only)

| Metric | Count |
|---|---:|
| Total users | 9 |
| Users with Staff links | 7 |
| Users with effective Staff links | 7 |
| Users with any role assignment | 6 |
| Users without an effective role | 3 |
| Effective SUPER_ADMIN | 1 |
| Effective WAKA_AKADEMIK | 1 |
| Effective WALI_KELAS | 4 |
| Duplicate user links / duplicate Staff links / orphan links | 0 / 0 / 0 |

All six effective role assignments have `scope_type=INSTITUTION`. This is observation, not permission to alter legacy scopes or class authorization. No class/homeroom data was queried. The three role-less accounts remain role-less; this review does not authorize provisioning them.

The complete role-code catalog is `SUPER_ADMIN`, `WAKA_AKADEMIK`, `WALI_KELAS`. The complete permission-code catalog is:

- `academic.dashboard.export`
- `academic.student_attendance.handover_complete`

Both existing permission grants belong to WAKA_AKADEMIK. SUPER_ADMIN and WALI_KELAS have no `role_permissions` rows. No user identifiers or personal details are needed to establish this result.

**Active-admin qualification:** `users.status` does not exist yet, so a persisted ACTIVE count cannot be queried or claimed. The one effective SUPER_ADMIN is active under the current legacy/default account contract (`status ?? 'ACTIVE'`); migration default ACTIVE would preserve it. This proves an existing authority to preserve, not successful post-migration HTTP login.

## 5. Migration review

Source: the three exact files under `application/web/database/migrations/` listed above.

| Migration | Design and compatibility |
|---|---|
| 000001 | Adds non-null varchar(20) `status`, default `ACTIVE`, normal status index. Existing 9 users receive ACTIVE. Down drops index then column. |
| 000002 / features | UUID primary key; unique code; required name/module; nullable description/required_permission; boolean default_enabled/system_enabled/is_toggleable all true; sort_order integer default 0; timezone-aware timestamps; module+sort index. |
| 000002 / overrides | UUID PK; bigint user/nullable actor FKs to users; UUID feature FK; all delete RESTRICT; non-null state default INHERIT; nullable reason; version integer default 1; unique user+feature; user+state index; timezone-aware timestamps. |
| 000002 / preferences | UUID PK; bigint user/nullable actor FKs with RESTRICT; required preference_key and JSONB preference_value; unique user+key; timezone-aware timestamps. |
| 000003 | Non-null boolean must_change_password default false. PostgreSQL grammar has no `after` modifier, so `after('status')` is not a PostgreSQL positional requirement. Down drops this column. |

The table rollback order is preferences → overrides → features. Across these migrations, reverse order is 000003 → 000002 → 000001. `required_permission` is a nullable string, **not** a foreign key; missing permission codes therefore do not prevent migration/seeder success. UUID generation is model-owned (`HasUuids`), not a DB default to assume for raw inserts. No enum CHECK for status/state or positive-value CHECK is declared; PostgreSQL integer types do not gain unsigned enforcement from the Blueprint annotation. These are descriptive boundaries of the accepted implementation, not new schema changes proposed here.

Installed Laravel migrator uses schema transactions when supported and `withinTransaction=true`; PostgreSQL supports them. This is per migration, not atomicity across all three migrations plus seeder. Earlier successful migrations may remain after a later failure.

PILOT users table total relation footprint was 49,152 bytes for 9 users. No other-backend lock on `public.users` was observed at inspection. That reduces expected work but **does not guarantee a lock-free future rollout**: ALTER TABLE and the non-concurrent status index can wait/block. Require a maintenance window, fresh lock checks, bounded lock/statement timeouts in the future execution connection, and abort on unexpected contention. No migration rehearsal or lock acquisition was performed against PILOT.

## 6. Registry bootstrap and idempotency

`application/web/database/seeders/UserAccessFeatureSeeder.php:14-31`:

- Uses `Permission::firstOrCreate` for `platform.user.manage`.
- Uses the normal SUPER_ADMIN `permissions()` relation and `syncWithoutDetaching` for its grant.
- Uses `Feature::updateOrCreate` on code for all 13 codes below.
- Does not create users, Staff, assignments, attendance, preferences, overrides, or provider configuration.

| Feature codes | Required permission |
|---|---|
| academic.dashboard; academic.attendance; academic.attendance_review; academic.reports | none |
| academic.students; academic.classes; academic.structure; academic.schedules; academic.subjects; academic.staff | academic.domain.manage |
| platform.user_access | platform.user.manage |
| platform.system_settings; ai.academic_assistant | platform.institution.manage |

Row creation is idempotent, but replay **resets feature definitions/defaults/system flags** to source values; it is not a no-op after administrator customization. The nullable SUPER_ADMIN lookup silently skips the grant if the role is absent; current PILOT has that role, but future execution must fail closed if it disappears. The seeder has no explicit encompassing transaction or AuditLogger calls. A partial seed must not be exposed to users, and no audit event should be invented as if the seeder emitted one.

**Migration alone is insufficient.** Required order is schema → specific registry/permission seed → complete registry verification → effective authorization verification → reopen application. The default `DatabaseSeeder` creates a test account and does not call this seeder; it must NOT be used for this rollout.

`EnsureFeatureAccess.php:14-18` preserves an unregistered **code** when the features table exists. It does not tolerate an absent table. Login also queries `user_preferences` (`AuthenticatedSessionController.php:90-108`). Thus neither schema-less new code nor schema-only/partially seeded new code is a safe normal-use checkpoint. Maintenance must cover the whole transition.

## 7. Confirmed blocker: legacy authority vs seeded feature gates

**ID:** `USER_ACCESS_PILOT_BOOTSTRAP_AUTHORITY_COMPATIBILITY`
**Disposition:** OPEN / BLOCKS CONTROLLED PILOT MIGRATION

Evidence chain:

1. Live read-only permission catalog contains neither `academic.domain.manage` nor `platform.institution.manage`.
2. `AcademicAuthorizationService.php:32-48,78-81` falls back to effective WAKA/SUPER_ADMIN roles when those authority permissions are absent. Current controller authorization therefore recognizes those legacy roles.
3. The seeder only creates `platform.user.manage`; it does not supply either missing authority permission or its role grants.
4. `FeatureAccessResolver.php:19-24` strictly requires a role-permission match for registered features. There is no legacy-role fallback and even an ENABLED override cannot bypass the requirement.
5. `routes/web.php:78-113` puts the six Academic master groups behind those registered gates. `:116` gates AI Provider System Settings by `platform.system_settings`.

| Existing role | Previously valid authorization path | Projected result after current seed only |
|---|---|---|
| SUPER_ADMIN | Academic full authority fallback | Six master groups denied by feature permission gate |
| WAKA_AKADEMIK | Academic full authority fallback | Same six master groups denied |
| SUPER_ADMIN | System Settings requires effective SUPER_ADMIN plus institutional fallback (`AiProviderConfigurationController.php:136-144`) | Denied by newly registered system_settings feature |
| WALI_KELAS | Scoped dashboard/attendance/review/report workflows, subject to existing business checks | No new required-permission denial from these four registry entries; scope/backend checks remain authoritative |
| SUPER_ADMIN | New User & Akses capability | Expected allowed after platform.user.manage grant and enabled registry row; not executed/verified on PILOT |

This is a **source + catalog projection**, not an executed HTTP rollout test. In particular, this review does not claim that current schema-less new-code HTTP routes work. The compatibility comparison is to the existing authorized business contract.

Green CI is not contradictory: `tests/Feature/Admin/UserAccessManagementTest.php:23-32` supplies `platform.institution.manage` in its admin fixture; `test_inherit_preserves_normal_baseline` checks academic.dashboard, which has no required permission. The suite also explicitly proves that ENABLED cannot bypass a missing permission. These checks do not prove preservation of the observed legacy PILOT catalog.

**Do not fix this by opportunistically adding permissions.** Creating either authority permission switches `authorityPermissionsAreConfigured()` globally, changing fallback behavior. A reviewed migration/bootstrap authority matrix must preserve both SUPER_ADMIN and WAKA authority without granting Wali institutional administration or weakening backend checks. No permission choice is self-ratified by this report.

## 8. Super Admin postflight design and password safety

Current legacy authority baseline: one effective institutional SUPER_ADMIN. The new platform.user.manage grant is narrowly targeted and would allow User & Akses under the current resolver. It does not solve the broader loss above.

After separately authorized, corrected rollout, in a fresh READ ONLY transaction:

1. Prove target identity and `transaction_read_only=on` again.
2. Count at least one `users.status='ACTIVE'` user with an effective SUPER_ADMIN assignment using Jakarta inclusive-start/exclusive-end dates.
3. Require exactly one `platform.user.manage` permission, the SUPER_ADMIN role-permission grant, and the `platform.user_access` registry row with system_enabled/default_enabled true and expected required_permission.
4. For the effective Super Admin candidates, resolve platform.user_access using the real `FeatureAccessResolver` (read-only, no HTTP login/session writes): require role_permission_allowed=true, scope_allowed=true, effective_enabled=true and no denial reason. Select only id/status for internal resolution, report aggregates only.
5. Resolve representative Academic and system settings features for current roles, and verify underlying controller/Academic authorization too. Require the approved pre/post authority matrix, not just the feature boolean. Cross-class and role-less denials remain mandatory; no extra assignment or override is used as a workaround.
6. Require existing-user count unchanged, all pre-existing users ACTIVE and must_change_password=false; no new overrides/preferences or account creation from bootstrap. Verify audit differences contain only separately approved deployment evidence and no password/secret values.

Both new defaults are non-null: ACTIVE and false. Therefore schema application alone should neither disable any current account nor force a password change. New-account/password-reset paths may set true later through application logic. No password resets, password/hash reads, temporary password issuance, or session invalidation occurred in this review.

## 9. AI and Academic operation separation

Resolved local configuration `academic.ai.assistant_enabled=false`; Public Academic AI remains OFF. `AcademicAiController.php:26-28` checks that global gate before orchestration. The registry entitlement does not modify configuration or activate provider runtime. Its institution permission requirement is not a substitute for the AI controller's separate authorization; no future AI activation readiness is claimed.

`ACADEMIC-WALI-PILOT-FIRST-DAY-CONTROLLED-ATTENDANCE-UAT = HOLD / HUMAN_OBSERVATION_REQUIRED` is preserved. No Academic transaction tables were queried or written by this review; existing provisioning evidence is not re-certified for a new horizon. Attendance entry/finalization, PRIMARY obligations, corrections, schedules, rosters, locks, and AI/provider state are untouched. `SOC-MD-06` and `IMP-S12-007=NOT_STARTED` are unchanged. This is not production readiness or staging/production authorization.

## 10. Future controlled execution contract — DESIGN ONLY, blocked now

1. Obtain owner-reviewed resolution of the authority compatibility blocker, focused legacy-catalog regression evidence, and exact executable CI. Pin clean Git HEAD and re-prove all pending migrations. No execution is authorized by this report.
2. Confirm a fresh protected PILOT backup with integrity/parse checks and a proven restore procedure; keep secrets and backup content out of Git. This review did not create or validate a fresh backup. Confirm maintenance access, responsible recovery operator, and rollback criteria before writes.
3. Close normal web/queue/scheduled writers for the controlled window under separate operational authorization. Fresh READ ONLY preflight: database imtaq, PostgreSQL 18.x, current schema absent, only the three intended migrations pending, accounts/roles stable, last-admin authority and locks acceptable, AI OFF. Record approved non-PII baselines. HOLD on drift/partial application.
4. Inspect/use the existing target guard. `MigrateWithTargetGuard` checks configured database/host/port and actual PostgreSQL database/port, then calls migrate with `--force`. Its actual host field derives from configured host, not an independent network environment attestation; a name alone is not proof of PILOT. Pair it with the established local environment identity and guarded pending-set check.
5. Conditional future command from application/web: `php artisan migrate:guarded imtaq --connection=pgsql --host=127.0.0.1 --port=5432`. This command migrates ALL pending files, so proceed only with an approved exact pending set; it does not itself guarantee the required bootstrap sequence or backup. Establish bounded transaction-local/connection timeouts without changing persistent runtime config.
6. While still closed, run only the reviewed registry bootstrap. Existing CLI form is `php artisan db:seed --database=pgsql --class=UserAccessFeatureSeeder --force`; do not run default DatabaseSeeder. The current seeder is blocked by section 7, so this is a tool reference, not an executable approval. Re-prove the connection used by the seed; the migration guard does not carry over to a new process. Define atomic seed handling or controlled partial-seed recovery in the authorized implementation contract.
7. Verify exactly three intended migration additions (43→46 if no drift), columns/defaults, FKs/RESTRICT/unique indexes/JSONB, all 13 expected codes and permission definitions/grants, no unintended data additions. Apply the approved authority matrix and all checks in section 8; never open merely because schema/seed commands exit zero.
8. Verify deployment evidence and audit boundaries. Current seeder does not emit domain audit events; any append-only deployment audit must have explicit execution authorization and a defined action/entity, never retroactively fabricated account actions. Compare approved account/role/permission deltas only. No historical AuditLog rewriting.
9. Independent fresh READ ONLY postflight, including AI OFF and unchanged Academic baseline where separately authorized for read. Reopen only if all checks pass; otherwise remain closed and select recovery scenario below. Human operational login/UAT is separate and must not be simulated with a password reset.
10. Save redacted execution manifest, exact HEAD/CI, allowed write counts, postflight, rollback disposition; return for audit and STOP.

## 11. Rollback assessment

| Scenario | Safe disposition |
|---|---|
| A. Migration fails before normal use | Remain in maintenance. Inspect migration log/schema read-only; PostgreSQL transactional migration failure need not undo earlier successful files. Recover only the proven applied subset in reverse dependency order with code/schema compatibility and backup. Do not use blind rollback/migrate:fresh. |
| B. Schema succeeds, registry seed fails | Do not reopen with incomplete registry. Inventory partial permissions/grants/features. After approved blocker resolution, a guarded idempotent bootstrap repair may be safer than dropping schema. Schema down does not remove seeded platform.user.manage/role_permissions; model the entire delta before any separately approved cleanup. |
| C. Schema + seed succeed, access verification fails | No manual broad grant/bypass or unregistered-code workaround. Keep maintenance; preserve evidence and use an approved narrowly scoped forward correction or coherent code+schema recovery before use. The current catalog would encounter this scenario if the blocker were ignored. |
| D. New real User & Akses data exists | Do not casually drop overrides/preferences/status flags or restore an old backup over new transactions. Preserve account/access/audit history. A reviewed forward fix is preferred; any restore requires reconciliation of post-backup facts and explicit authority. |

No rollback or cleanup was executed. Password/role/permission data, historical facts, and provider configuration must not be collateral recovery targets.

## 12. Acceptance traceability and verification

Acceptance IDs `SAUAP-PMR-01` through `SAUAP-PMR-15` correspond one-to-one to phases 1–15 of the user contract. All review obligations are completed. Phases 7/8/14 identify a blocking access-compatibility result; review completion does not mean migration acceptance.

- Git identity/parity/clean-entry and tested-source equivalence: PASS.
- Authoritative CI metadata verification: PASS on tested 465be64 only.
- Two read-only database inspections, identity/schema/accounts/catalog checks: PASS.
- Migration/default/bootstrap/tooling review: complete; rollout authorization HOLD.
- No new application regression was run: no executable change, and PILOT-writing test/seeder/migration runs are forbidden. Existing CI remains inherited evidence, not a new local test result.
- Repository structure, tracked/untracked whitespace checks, JSON parse, and evidence/routing invariant checks: PASS at closeout. An initial Markdown trailing-space check failed; only those newly added formatting spaces were removed before the clean recheck.
- Roadmap/product-completion denominator is unchanged; no project-progress regeneration or percentage increase is justified by this readiness review.

## Closeout

Changed governance/evidence files only:

- `codex/REVIEWS/SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-READINESS-2026-10-02.md` (new)
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`
- `codex/WORK_LOG.md`
- `PROJECT_STATE.json`
- `EVIDENCE_INDEX.json`

No process is running. These files are saved locally, not committed/pushed. Resume at owner/ChatGPT audit, not a database execution step. Review completion is 100% (15 review phases addressed); overall product completion was not recalculated.

```text
PROJECT = SISTEM-IMTAQ
TASK = SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-READINESS-REVIEW
REVIEW_STATUS = COMPLETED
READINESS_DECISION = HOLD
BLOCKER = USER_ACCESS_PILOT_BOOTSTRAP_AUTHORITY_COMPATIBILITY
STARTING_HEAD = 67672bdbf1dab18634c07517316780891debf467
FINAL_HEAD = 67672bdbf1dab18634c07517316780891debf467
TESTED_EXECUTABLE_HEAD = 465be64a6d5263def7d1eb3554e35590426346ec
AUTHORITATIVE_CI = 36976686299 / SUCCESS / TESTED_EXECUTABLE_HEAD_ONLY
PARTIAL_SCHEMA_STATE = NO
PENDING_USER_ACCESS_MIGRATIONS = 3
EFFECTIVE_LEGACY_SUPER_ADMIN = 1
TRANSACTION_READ_ONLY = ON
PILOT_DATABASE_WRITE = NONE
APPLICATION_SOURCE_CHANGED = NO
MIGRATION_OR_SEED_EXECUTED = NO
PUBLIC_ACADEMIC_AI = OFF
ACADEMIC_FIRST_DAY_UAT = HOLD / HUMAN_OBSERVATION_REQUIRED
PRODUCTION_READINESS = NOT_ASSESSED
NEXT_ATOMIC_TASK = RETURN_TO_CHATGPT_FOR_USER_ACCESS_PILOT_MIGRATION_READINESS_AUDIT
SAFE_TO_CLOSE = YES
STOP = YES
```

Recommended subsequent engineering decision, only after owner audit: `SUPER-ADMIN-USER-ACCESS-BOOTSTRAP-AUTHORITY-COMPATIBILITY-DESIGN`, preserving the legacy valid-role matrix without broadening permissions. It is not started or authorized here.

Owner checkpoint choices (estimates, neither started):

1. Recommended: audit this completed read-only decision in ChatGPT, approximately 10–20 minutes.
2. After accepting the finding and issuing separate authorization: a narrowly scoped compatibility design/test-plan checkpoint, approximately 30–60 minutes. No PILOT permission or migration writes are implied.
