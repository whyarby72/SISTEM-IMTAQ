# FOUNDATION-DB-R3 — PostgreSQL Full-Suite Regression Diagnosis

## Closeout

- **Task:** `FOUNDATION-DB-R3-POSTGRES-REGRESSION-DIAGNOSIS`
- **Mode:** read-only diagnosis
- **Branch:** `chore/foundation-db-r3-postgres-regression-diagnosis`
- **Baseline HEAD:** `35bed6d7657b7ff21eaabb2e1e81feefc61158a9`
- **Exact CI run:** [36402569265](https://github.com/whyarby72/SISTEM-IMTAQ/actions/runs/36402569265)
- **Exact CI head:** `349ef67ac6e9e6a2d02a25e137084b00fe820c21`
- **Result:** `16 failed, 15 passed, 490 warnings, 2171 assertions`
- **FDB-R3 status:** `COMPLETED / DIAGNOSIS_ONLY / PASS`
- **IMPLEMENTATION_AUTHORIZATION:** `NOT_AUTHORIZED`
- **Recommended next task:** `FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`

R3 made no application, test, workflow, PHPUnit, database-config, migration,
dependency, schema, persistent-database, provider-state, or deployment change.

## Executive findings

The 16 failures are individually classifiable. No evidence requires weakening
the PostgreSQL schema. The canonical constraints are behaving as designed:

- `ai_provider_credentials.secret_last4` is `varchar(4)`;
- audit chronology is `occurred_at`, not `created_at`, and audit rows have no
  Laravel timestamp pair;
- audit `new_values` is JSONB;
- `class_sessions_active_no_overlap` rejects overlapping active sessions;
- `chk_class_sessions_session_status` rejects values outside the canonical
  session-status vocabulary.

The five time-sensitive failures are held at **TZ-C** rather than being
silently called either a fixture-only issue or an application defect. The
repository states `Asia/Jakarta` as the application timezone and uses
`timestampTz`, but it does not establish the PostgreSQL session timezone or a
single authoritative rule for how naive test/application timestamp inputs are
interpreted. The CI failures are therefore real evidence of an unresolved
timezone/storage contract, not enough evidence for an R3 application fix.

## Exact 16-failure inventory

| # | Test class / method | Failure line | PostgreSQL error or assertion | Canonical contract | Primary class | Secondary tag | Root cause | Production relevance | Proposed remediation | Exact proposed write file(s) | Protected impact | Confidence |
|---:|---|---:|---|---|---|---|---|---|---|---|---|---|
| 1 | `AcademicAiRuntimeTest::test_openai_transport_sends_store_false_auto_choice_no_parallel_and_client_trace` | 49 | `22001`, value too long for `varchar(4)` | `secret_last4` stores exactly four characters | `TEST_FIXTURE` | PostgreSQL strictness / length | Synthetic fixture supplies a five-character suffix | None after fixture correction; schema is production-safe | Use a four-character synthetic suffix; retain the schema | `application/web/tests/Feature/Academic/AI/AcademicAiRuntimeTest.php` | No migration or model change | HIGH |
| 2 | `AiProviderConfigurationTest::test_synthetic_function_roundtrip_requires_not_found_and_final_completion` | 500 | `42703`, `audit_logs.created_at` does not exist | Audit chronology is `occurred_at`; audit table has no Laravel timestamps | `TEST_ASSERTION` | schema-field mismatch | Test helper orders by a non-canonical column | Assertion-only; current audit schema remains authoritative | Order by `occurred_at` or use the canonical audit model/query contract | `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php` | No audit migration or historical rewrite | HIGH |
| 3 | `AiProviderConfigurationTest::test_invalid_input_provider_error_uses_precise_failure_category` | 523 | `42703`, `audit_logs.created_at` does not exist | Same `occurred_at` audit contract | `TEST_ASSERTION` | schema-field mismatch | Failure-audit assertion uses `latest('created_at')` | Assertion-only | Use `occurred_at` in the assertion | `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php` | No audit migration or historical rewrite | HIGH |
| 4 | `AiProviderConfigurationTest::test_d3_provider_error_audit_whitelists_safe_fields_only` | 579 | `42703`, `audit_logs.created_at` does not exist | Same `occurred_at` audit contract and JSON metadata | `TEST_ASSERTION` | schema-field mismatch / secret safety test | Safe-audit assertion cannot reach its metadata checks because ordering is invalid | The safety behavior is unverified by this test until assertion is corrected | Use canonical `occurred_at` ordering, then retain all secret/body exclusion assertions | `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php` | No raw provider-body persistence change | HIGH |
| 5 | `AcademicRoleDashboardServiceTest::test_today_attendance_distinguishes_finalized_due_and_missing_sessions` | 235 | Expected `due_sessions=2`, received `0` | Today/dashboard queries must use the agreed local operating date against `timestampTz` | `CONTRACT_GAP` | TZ-C / timestamptz boundary | Naive fixture timestamps, PostgreSQL timezone interpretation, and app-local clock are not governed by one explicit contract | Potentially production-relevant for Today/dashboard counts | Establish timezone/storage/query authority first; then adjust only the affected fixture or service/query scope | `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; conditional service/config files only after TZ decision | Do not change migrations or constraints in R3 | MEDIUM |
| 6 | `AcademicRoleDashboardServiceTest::test_live_attendance_metrics_exclude_sessions_that_have_not_ended` | 600 | Expected `upcoming_sessions=1`, received `3` | Future/past classification must be deterministic in the operating timezone | `CONTRACT_GAP` | TZ-C / clock boundary | Same unresolved interpretation of `timestampTz` values versus the injected/test clock | Potentially production-relevant | Resolve TZ-C; add explicit timezone-aware fixture values and/or narrowly scoped service coverage | `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; conditional `AcademicRoleDashboardService.php` | No schema weakening | MEDIUM |
| 7 | `AcademicRoleDashboardServiceTest::test_wali_live_status_includes_session_reaching_class_through_scope_group` | 618 | Expected `due_sessions=2`, received `0` | Joint scope and due-state counts must use the same authoritative clock/storage rule | `CONTRACT_GAP` | TZ-C / joint scope + timestamptz | Date-window/state comparison is not explainable without the missing timezone rule | Potentially production-relevant for Wali dashboard | Resolve timezone authority before deciding whether source or fixture is wrong; preserve joint-scope logic | `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; conditional dashboard service/query files | No attendance-semantic change in R3 | MEDIUM |
| 8 | `AcademicTodaySessionServiceTest::test_today_window_and_open_state_classification_are_deterministic` | 40 | Expected first state `IN_PROGRESS`, received `UPCOMING` | `AcademicTodaySessionService` explicitly returns `Asia/Jakarta`, while session storage is `timestampTz` | `CONTRACT_GAP` | TZ-C / operational-state boundary | Fixture timestamps are naive and no DB session-timezone/write convention is declared | Potentially production-relevant | Freeze timestamp input/storage authority, then make the test data explicit; only then assess service logic | `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`; conditional `AcademicTodaySessionService.php`/DB connection scope | Do not alter `timestampTz` migration in R3 | MEDIUM |
| 9 | `AcademicTodaySessionServiceTest::test_completed_and_overdue_states_are_distinguished` | 53 | Expected `overdue_unfinished_count=1`, received `0` | End-time comparison must be local-time deterministic | `CONTRACT_GAP` | TZ-C / operational-state boundary | Same unresolved absolute-time interpretation | Potentially production-relevant | Same TZ-C decision and targeted replay | `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`; conditional service/config scope | No status or migration weakening | MEDIUM |
| 10 | `AcademicTodaySessionServiceTest::test_planned_and_confirmed_share_timestamp_classification_but_keep_raw_status` | 136 / helper 242 | `23P01`, `class_sessions_active_no_overlap` | Active sessions for one class may not overlap | `TEST_FIXTURE` | exclusion constraint | Test intentionally inserts two identical active ranges to compare statuses | No production defect proven; test setup violates canonical schedule integrity | Use non-overlapping timestamps or a non-persisted unit-level state fixture; do not drop the exclusion | `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php` | Constraint remains untouched | HIGH |
| 11 | `AcademicTodaySessionServiceTest::test_unknown_raw_status_is_not_silently_treated_as_active` | 171 / helper 242 | `23514`, `chk_class_sessions_session_status` | Persisted `session_status` is a closed canonical vocabulary | `TEST_FIXTURE` | CHECK constraint / invalid fixture | Test tries to persist `UNKNOWN`, which the schema correctly rejects before service behavior can be tested | No production defect proven; unknown-status behavior needs a non-persisted test seam or contract decision | Test unknown mapping without violating the DB contract, or explicitly document unknown as impossible persisted data | `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php` | Do not broaden the CHECK in R3 | HIGH |
| 12 | `SubstitutionServiceTest::test_substitution_rejects_replacement_teacher_conflict_before_mutation` | 56 / helper 131 | `23P01`, `class_sessions_active_no_overlap` | Active class-session ranges cannot overlap | `TEST_FIXTURE` | exclusion constraint | Conflict fixture inserts an overlapping session before the service can evaluate teacher conflict | No production defect proven; database rejects invalid setup earlier | Create a valid non-overlapping session or test the service conflict precondition without invalid class-session persistence | `application/web/tests/Feature/Academic/SubstitutionServiceTest.php` | No constraint weakening | HIGH |
| 13 | `SubstitutionServiceTest::test_non_overlapping_adjacent_and_cancelled_sessions_do_not_conflict` | 87 / helper 131 | `23P01`, overlapping `PLANNED` session | A session must be cancelled before it can be outside the active exclusion predicate | `TEST_FIXTURE` | exclusion constraint / operation ordering | Fixture inserts the second session as `PLANNED`, then updates it to `CANCELLED`; PostgreSQL enforces the constraint at insert time | No production defect proven | Persist the test session in a valid non-overlapping slot, or use an explicit cancellation transition path that is legal under the schema | `application/web/tests/Feature/Academic/SubstitutionServiceTest.php` | No migration or constraint edit | HIGH |
| 14 | `SwapServiceTest::test_swap_rejects_expected_substitute_overlap_without_partial_change` | 58 | `23P01`, overlapping active class-session ranges | Same exclusion contract | `TEST_FIXTURE` | exclusion constraint | Conflict fixture inserts an overlapping active session before SwapService runs | No production defect proven | Construct conflict through teacher participation on valid non-overlapping sessions, or isolate the teacher-overlap precondition | `application/web/tests/Feature/Academic/SwapServiceTest.php` | No constraint weakening | HIGH |
| 15 | `TeacherAttendanceServiceTest::test_participation_from_another_session_is_rejected_without_mutation` | 120 | `23P01`, duplicate active range | Same class cannot have overlapping active sessions | `TEST_FIXTURE` | exclusion constraint / replicate fixture | Test replicates a session with the same active time range, so persistence fails before participation mismatch is tested | No production defect proven | Use a distinct valid time range while preserving the mismatched participation identity | `application/web/tests/Feature/Academic/TeacherAttendanceServiceTest.php` | No migration or schema change | HIGH |
| 16 | `CoreContractsTest::test_audit_payload_redacts_nested_credential_like_values` | 63 | `22P02`, `%` is invalid JSON; JSONB equality receives a wildcard scalar | `audit_logs.new_values` is JSONB, not text `LIKE` data | `TEST_ASSERTION` | JSONB typing | `assertDatabaseMissing` passes a SQL wildcard as a JSONB equality value | Assertion-only; redaction code itself was reached before query failure | Assert decoded JSON structure or use a PostgreSQL JSON-aware predicate | `application/web/tests/Feature/Shared/CoreContractsTest.php` | No JSONB schema change or audit rewrite | HIGH |

## Timezone gate conclusion

**All five time-sensitive failures (#5–#9) are TZ-C.**

Evidence for the gate:

1. `config/app.php` declares `APP_TIMEZONE` default `Asia/Jakarta`.
2. `class_sessions.planned_start_at` and `planned_end_at` are `timestampTz`.
3. `AcademicTodaySessionService` converts its clock to the app timezone, but
   the CI fixtures insert timezone-less strings.
4. `config/database.php` does not set a PostgreSQL session timezone, and the
   repository does not state whether all application writes must pass explicit
   `Asia/Jakarta`-aware values or whether the connection must set a session
   timezone.
5. Therefore the observed UTC/local shift is plausible, but the repository
   does not provide enough authority to label it TZ-A (fixture-only) or TZ-B
   (application defect).

No application defect is declared for these failures in R3. If the owner
freezes an explicit timezone/storage contract and replay still shows wrong
operational states, the resulting task must be promoted to TZ-B with
`ACADEMIC_DOMAIN` impact and separately scoped service/config changes.

## PostgreSQL constraint and transaction analysis

- `secret_last4` length, audit `occurred_at`, JSONB typing, the exclusion
  constraint, and the session-status CHECK are canonical protections. None is
  a schema defect on current evidence.
- Exact run `36402569265` contains the `23P01`, `23514`, `42703`, `22001`, and
  `22P02` errors listed above. It does **not** contain a primary `25P02`
  current-transaction-aborted failure, `23505` duplicate-key failure, or
  `23503` foreign-key failure.
- The exclusion/CHECK messages are not secondary noise: each maps directly to
  a failed test and is counted exactly once in the 16 rows.
- Duplicate/FK/aborted-transaction messages from expected negative-test
  patterns are not independently countable in this exact log. No evidence
  supports adding them to the 16 or changing transaction handling in R3.
- A future remediation replay must keep each intentionally failing DB operation
  isolated or rolled back before further assertions, so PostgreSQL cannot hide
  a later assertion behind an aborted transaction.

## 490-warning classification

The exact run reports 490 warnings, while the 16 failures are emitted in the
same PHPUnit run. The warning stream is dominated by existing contract/source
inspection tests that report `file_get_contents(...)`-style warnings and the
suite's non-blocking warning presentation across Academic/AI/Foundation tests.
The exact run gives no evidence that the warning count caused any of the 16
failures or that PostgreSQL introduced a new warning class. Treat the warning
count as **pre-existing/non-blocking warning debt**, not as a broad cleanup
scope. Only a warning proven causal to one of the 16 may enter R4.

## CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY

Classification: **EXPOSED_IN_STEP_ENV_LOG / REMEDIATION_REQUIRED**.

The exact Actions log renders the `env:` block for the foundation step and
shows the literal disposable database password. It is CI-only and not a pilot,
staging, or production credential, but it is still unnecessary log exposure.

Minimum R4 remediation:

- keep the PostgreSQL service disposable and CI-only;
- stop placing the literal password in a step/job environment that GitHub
  prints in the command-group header;
- inject it through a masked CI mechanism or a runtime-only shell setup that
  is not echoed, while retaining the identity guard;
- add an assertion that the exact credential value is absent from captured
  logs, without introducing persistent-environment secrets.

R3 does not edit the workflow.

## Exact proposed remediation scope

### Test-only corrections

- `application/web/tests/Feature/Academic/AI/AcademicAiRuntimeTest.php`
- `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php`
- `application/web/tests/Feature/Shared/CoreContractsTest.php`
- `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `application/web/tests/Feature/Academic/SubstitutionServiceTest.php`
- `application/web/tests/Feature/Academic/SwapServiceTest.php`
- `application/web/tests/Feature/Academic/TeacherAttendanceServiceTest.php`

These are proposed R4 writes only. No test was modified by R3.

### Conditional application/configuration scope

Only after the owner closes TZ-C:

- `application/web/app/Domains/Academic/Services/AcademicTodaySessionService.php`
- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- possibly the PostgreSQL connection timezone configuration, exact file to be
  selected by the accepted contract.

No conditional application write is authorized by R3.

### Workflow/security scope

- `.github/workflows/application-foundation.yml` only for the CI credential
  visibility remediation and its log-absence test.

### Explicitly protected

- all applied migration files, especially the class-session, audit, and AI
  provider migrations;
- `application/web/config/database.php` until TZ-C is decided;
- pilot/staging/production databases and credentials;
- provider configuration/active pointer/OpenAI state;
- PostgreSQL constraints and extensions;
- unrelated Academic semantics, attendance data, and report behavior.

## R4 regression plan (proposal only)

1. Decide and document the timezone/storage/session-timezone contract.
2. Correct the eight test files above without weakening constraints.
3. Add focused PostgreSQL tests for JSONB assertions, canonical audit time,
   exclusion/CHECK-valid fixtures, and timezone boundaries.
4. Remediate CI password log visibility and assert no literal appears in logs.
5. Run focused Shared Core, Academic, AI, and foundation guard suites.
6. Run migration-from-zero and the full PostgreSQL 18.6 suite.
7. Keep any remaining failure explicit; do not call CI PASS on a partial run.

## Routing

- `FOUNDATION_SQLITE_MIGRATION_COMPATIBILITY`: **RESOLVED**.
- `FOUNDATION_POSTGRESQL_FULL_SUITE_REGRESSION`: **UNRESOLVED**; owner moves
  to the proposed R4 implementation task after ChatGPT audit.
- `CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY`: **UNRESOLVED**, included in R4.
- Canonical queue marker `SOC-MD-06`: unchanged.
- Public Academic AI: **OFF**.
- Database write, migration, import, seed, provider mutation: **NONE**.

## Recommended next atomic task

`FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`

R4 must first obtain owner approval for the TZ-C contract. If that authority
cannot be established, HOLD the time-sensitive remediation portion and proceed
only with independently classified test assertions/fixtures and CI log
visibility, under a separately approved scope.

**NEXT_ATOMIC_TASK:** `RETURN_TO_CHATGPT_FOR_FOUNDATION_DB_R3_AUDIT`
