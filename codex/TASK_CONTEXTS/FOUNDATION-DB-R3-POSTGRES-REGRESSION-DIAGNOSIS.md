# FOUNDATION-DB-R3 — PostgreSQL Full-Suite Regression Diagnosis

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** READ_ONLY_REGRESSION_DIAGNOSIS  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** chore/foundation-db-r3-postgres-regression-diagnosis  
**State-basis:** 349ef67ac6e9e6a2d02a25e137084b00fe820c21

## Authority

FOUNDATION-DB-R2 is accepted as:

`COMPLETED / PASS_WITH_NEW_BLOCKER`

PostgreSQL ephemeral CI infrastructure is implemented and the prior
`FOUNDATION_SQLITE_MIGRATION_COMPATIBILITY` blocker is resolved.

Exact final-HEAD CI evidence:

- run: `36402569265`
- head: `349ef67ac6e9e6a2d02a25e137084b00fe820c21`
- PostgreSQL 18.6 service: PASS
- PHP 8.4.x + pdo_pgsql: PASS
- identity guard: PASS
- migration-from-zero: PASS
- btree_gist / exclusion-schema assertions: PASS
- foundation PHPUnit: FAIL
- summary: `16 failed, 15 passed, 490 warnings, 2171 assertions`

Active blocker:

`FOUNDATION_POSTGRESQL_FULL_SUITE_REGRESSION`

## Goal

Classify every one of the 16 exact-current CI failures before any remediation.

Each failure must be assigned exactly one primary class:

- `TEST_FIXTURE`
- `TEST_ASSERTION`
- `APPLICATION_DEFECT`
- `CONTRACT_GAP`

Secondary tags are allowed, for example:
- PostgreSQL strictness
- timezone semantics
- JSONB typing
- schema-field mismatch
- exclusion constraint
- CHECK constraint
- test isolation
- transaction behavior

Do not patch code in R3.

## Known evidence that must be verified, not assumed

The final CI logs show at least these failure families:

1. AI provider fixture:
   `secret_last4` receives a value longer than schema `varchar(4)`.

2. Audit tests:
   assertions/query helpers order `audit_logs` by `created_at`, while the
   canonical audit schema/model uses `occurred_at` and has no timestamps.

3. JSONB assertion:
   a test compares JSONB `new_values` to a wildcard scalar such as
   `%do-not-store%`, which PostgreSQL rejects as invalid JSON input.

4. Academic session fixtures:
   fixtures create overlapping active sessions rejected by
   `class_sessions_active_no_overlap`.

5. Academic session fixture/status:
   a fixture uses a `session_status` rejected by the canonical CHECK
   constraint.

6. Academic Today/Dashboard behavior:
   PostgreSQL exact-current CI reports mismatches such as:
   - expected IN_PROGRESS but received UPCOMING;
   - expected due session counts but received 0;
   - expected upcoming session count 1 but received 3.

These time-sensitive failures MUST NOT be labeled fixture-only without
examining:
- Carbon test clock semantics;
- application timezone `Asia/Jakarta`;
- PostgreSQL `timestamptz` hydration/comparison behavior;
- query range boundaries;
- model casts;
- whether service logic uses injected/requested clock vs global `Carbon::now()`;
- whether SQLite previously masked a production-relevant defect.

## Required investigation

Inspect exact-current sources and logs for all 16 failures.

At minimum inspect:

- GitHub Actions run `36402569265`
- `application/web/tests/Feature/Academic/AI/AcademicAiRuntimeTest.php`
- `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php`
- `application/web/tests/Feature/Shared/CoreContractsTest.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`
- `application/web/tests/Feature/Academic/SubstitutionServiceTest.php`
- `application/web/tests/Feature/Academic/SwapServiceTest.php`
- `application/web/tests/Feature/Academic/TeacherAttendanceServiceTest.php`
- corresponding Academic services/models
- canonical audit model/schema
- canonical AI provider credential schema/model
- class-session schema/constraints
- timezone/app configuration
- `RefreshDatabase` / test isolation behavior where relevant

Use targeted search to locate every failing test file/line from the exact CI
log. Do not infer the complete inventory from the R2 manifest alone.

## Mandatory output table

The diagnostic artifact must contain one row per exact-current failure with:

- ordinal 1..16
- test class
- test method
- failure line
- PostgreSQL error/assertion
- canonical schema/service contract
- primary classification
- secondary tag
- root cause
- production relevance
- proposed remediation
- exact proposed write file(s)
- protected file impact
- confidence: HIGH / MEDIUM / LOW

No failure may be omitted or merged away.

## Timezone/application defect gate

For Today/Dashboard failures, determine whether the root cause is:

### TZ-A — Test fixture/clock mismatch
Production service behavior is correct and the fixture encoded SQLite/local
timezone assumptions.

### TZ-B — Application defect
Service/query/model logic mishandles PostgreSQL timestamptz or mixes clocks,
causing production-relevant wrong operational states/counts.

### TZ-C — Contract gap
Current repository has no authoritative timezone/storage/query rule sufficient
to decide.

If TZ-B:
- identify exact business-source file(s);
- classify impact at least `ACADEMIC_DOMAIN`;
- do not fix in R3;
- recommend a separately scoped application remediation task.

If TZ-C:
- HOLD the relevant remediation portion until authority is established.

## Constraint rule

PostgreSQL constraints are authoritative unless repository evidence proves a
schema defect.

Do NOT recommend weakening:
- `secret_last4 varchar(4)`
- audit `occurred_at` contract
- JSONB typing
- `class_sessions_active_no_overlap`
- session-status CHECK

merely to preserve SQLite-era test behavior.

If a constraint itself is proven inconsistent with an approved domain
contract, classify as `CONTRACT_GAP`; do not edit migrations in R3.

## Transaction/test isolation analysis

The exact log also contains PostgreSQL duplicate-key, FK, and aborted
transaction messages after expected constraint failures.

Determine whether these are:
- expected secondary noise from intentionally failing DB operations;
- evidence that a test catches a DB exception inside a transaction and then
  continues using an aborted PostgreSQL transaction;
- evidence of cross-test state leakage;
- or independent failures.

Do not count secondary log noise as additional primary failures unless it maps
to an exact failed test.

## CI credential log debt

R2 used a CI-only disposable PostgreSQL credential, but the literal value was
visible in GitHub Actions logs/environment output.

Classify this as:
`CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY`

R3 must propose a minimum remediation that:
- keeps the credential CI-only and disposable;
- prevents the literal credential from appearing in logs;
- does not introduce pilot/staging/production secrets;
- does not require production secret stores.

Do not change the workflow in R3.

## Warning debt

The run reports 490 warnings.

R3 must:
- identify whether the warning class is pre-existing/non-blocking or caused by
  PostgreSQL transition;
- summarize by category;
- do not expand into broad warning cleanup unless a warning is causally linked
  to one of the 16 failures.

## State/evidence reconciliation

R3 routing/closeout must correct durable evidence so that:

- exact-current CI points to run `36402569265`;
- exact-current head points to
  `349ef67ac6e9e6a2d02a25e137084b00fe820c21`;
- SQLite migration blocker remains RESOLVED;
- PostgreSQL full-suite blocker remains UNRESOLVED until remediation;
- stale `NEXT_ATOMIC_TASK = EXECUTE_FOUNDATION_DB_R2_FROM_REPOSITORY` is
  replaced by R3 routing;
- obsolete evidence debt saying exact-current CI still needs replay after the
  PHP/dependency decision is removed or marked resolved;
- public Academic AI remains OFF.

## Required artifact

Create:

`codex/DIAGNOSTICS/FOUNDATION-DB-R3-POSTGRES-REGRESSION-DIAGNOSIS-2026-09-28.md`

It must contain:

- exact 16-failure inventory table;
- root-cause classification;
- timezone gate conclusion TZ-A / TZ-B / TZ-C;
- transaction/isolation conclusion;
- warning summary;
- credential-log debt conclusion;
- exact remediation write scope grouped by classification;
- protected zones;
- regression plan;
- one recommended next task ID or explicit HOLD;
- `IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`.

## Allowed writes

Only diagnosis/routing/evidence:

- `codex/DIAGNOSTICS/FOUNDATION-DB-R3-POSTGRES-REGRESSION-DIAGNOSIS-2026-09-28.md`
- `PROJECT_STATE.json`
- `TEST_MATRIX.csv`
- `EVIDENCE_INDEX.json`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`

## Forbidden

Do not modify:

- application/business source
- tests/fixtures/assertions
- workflow implementation
- PHPUnit configuration
- TestCase/test guard implementation
- config/database.php
- any migration
- database schema/constraints
- Composer/PHP/dependencies
- provider/OpenAI state
- pilot/staging/production DB or credentials
- deployment
- main branch

Do not create or mutate a persistent database.

A disposable local/CI reproduction is allowed only if it is already available
and the existing fail-closed test DB guard proves it is safe. Do not create new
infrastructure as part of R3.

## Acceptance criteria

- FDB-R3-AC-01 exact final R2 HEAD/run evidence reconciled.
- FDB-R3-AC-02 all 16 failures individually inventoried.
- FDB-R3-AC-03 every failure has one primary classification.
- FDB-R3-AC-04 canonical schema/constraint evidence used.
- FDB-R3-AC-05 timezone failures resolved to TZ-A/TZ-B/TZ-C with evidence.
- FDB-R3-AC-06 transaction/isolation secondary noise analyzed.
- FDB-R3-AC-07 CI credential log debt classified with minimum fix proposal.
- FDB-R3-AC-08 exact remediation write scope proposed without implementation.
- FDB-R3-AC-09 routing/evidence debt reconciled.
- FDB-R3-AC-10 no application/test/workflow/schema implementation mutation.

## Closeout

Return:

`FOUNDATION-DB-R3 = COMPLETED / DIAGNOSIS_ONLY / PASS`

or HOLD if material failures cannot be safely classified.

Then STOP for ChatGPT audit.
