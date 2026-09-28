# FOUNDATION-TZ-D1 — Timezone & Timestamp Storage Authority

## Decision-design closeout

- **Task:** `FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY`
- **Branch:** `chore/foundation-tz-d1-timezone-storage-authority`
- **Baseline:** `2d183ded78b6e3da4358ab499caad4d2beddea20`
- **Mode:** decision/design only
- **RECOMMENDED_OPTION:** `A`
- **OWNER_DECISION_STATUS:** `PENDING_CHATGPT_AND_PROJECT_OWNER_AUDIT`
- **IMPLEMENTATION_AUTHORIZATION:** `NOT_AUTHORIZED`
- **Next atomic task after approval:** `FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`

No application source, test, configuration, workflow, migration, schema,
dependency, data, provider, or deployment file was changed by D1.

## Problem and root cause

The exact PostgreSQL foundation run `36402569265` on CI head
`349ef67ac6e9e6a2d02a25e137084b00fe820c21` reported 16 failures. R3 isolated
five Today/dashboard failures as `CONTRACT_GAP / TZ-C` because the repository
has a business timezone declaration but no complete cross-runtime contract for
naive local inputs, PostgreSQL session timezone, `timestamptz` boundaries, or
display/report conversion.

This is not evidence that PostgreSQL `timestamptz` or the existing migrations
are defective. It is an authority gap between:

- `config/app.php` default `Asia/Jakarta`;
- `class_sessions.planned_start_at` and `planned_end_at` stored as
  `timestampTz`;
- `AcademicTodaySessionService`, which converts its clock to the app timezone;
- dashboard code that uses global `Carbon::now()` for temporal classification;
- test fixtures that persist timezone-less timestamp strings; and
- `config/database.php`, which has no explicit PostgreSQL session timezone.

## Repository evidence

1. `application/web/config/app.php` sets `APP_TIMEZONE` with default
   `Asia/Jakarta`.
2. `application/web/config/database.php` defines PostgreSQL connection fields
   but no explicit `timezone`/session initialization contract.
3. `ClassSession` casts planned and actual timestamps to Laravel `datetime`,
   without an explicit domain value object or input normalization boundary.
4. The class-session migration uses `timestampTz`; PostgreSQL stores an
   absolute instant and renders it according to the connection/session display
   timezone when queried.
5. `AcademicTodaySessionService` creates a local `Asia/Jakarta` clock,
   computes start-of-day and next-day-exclusive boundaries, then compares
   hydrated session timestamps to that clock.
6. `AcademicRoleDashboardService` uses `Carbon::now()` and compares hydrated
   session times for upcoming, in-progress, and due states. Its date-period
   query receives caller-provided boundaries but their timezone contract is
   not centrally enforced.
7. The five TZ-C tests use values such as `2026-09-12 10:00:00` without an
   offset while asserting local `Asia/Jakarta` operational states.
8. `docs/02_architecture/POSTGRESQL_SCHEMA.md` says `timestamptz` is the
   technical timestamp type and names `Asia/Jakarta` as the app timezone, but
   does not define the full input/session/query contract.

## Options evaluated

### Option A — Asia/Jakarta business / UTC technical / aware boundaries

**Candidate recommendation.** Business users operate on `Asia/Jakarta`; every
human local input is interpreted in that zone at the application/domain
boundary; persistence stores an absolute instant in `timestamptz`; PostgreSQL
sessions use an explicit UTC baseline where supported; local-day boundaries are
constructed in `Asia/Jakarta` and converted to instants before querying.

Advantages:

- removes dependence on runner, worker, pool, or local PostgreSQL defaults;
- matches the existing application timezone;
- preserves `timestamptz` as an instant representation;
- makes Today/reporting semantics explicit;
- works consistently across web, queue, CLI, CI, and integrations;
- requires no edit to applied migrations or historical data.

Costs:

- requires an explicit normalization/validation boundary;
- requires timezone-aware test fixtures and focused boundary tests;
- may expose historical rows that were written from ambiguous local strings.

### Option B — Asia/Jakarta application and PostgreSQL session

PostgreSQL sessions would be explicitly set to `Asia/Jakarta`, and naive
database-bound values would depend on that session setting.

Advantages:

- familiar local rendering in interactive SQL and some legacy code paths;
- may reduce visible offset surprises for naive database-bound strings.

Risks:

- correctness depends on every web, queue, CLI, worker, pool, CI and admin
  connection applying the same session setting;
- a missed connection initialization silently changes interpretation;
- integrations and raw SQL remain sensitive to session defaults;
- UTC-based technical tooling and logs become less uniform;
- connection reuse can make session state an operational hidden dependency.

Option B is not preferred unless the Project Owner explicitly accepts the
session-wide dependency and mandates enforcement for every connection type.

### Option C — HOLD

HOLD is the safe result if the owner cannot approve a single business/input/
storage/session/query contract. Under HOLD, the five TZ-C failures remain
unclassified between TZ-A and TZ-B and no temporal remediation is authorized.

## Candidate timestamp contract — Option A

This is a design proposal, not an activated policy.

| Boundary | Contract |
|---|---|
| Institutional/business timezone | `Asia/Jakarta` is the institutional operating timezone and local calendar authority. Asia/Jakarta has no daylight-saving transitions; local dates remain stable at UTC+07:00. |
| UI local date/time | A schedule time entered without an offset is explicitly interpreted as `Asia/Jakarta` at the application boundary. The raw string must not be passed directly to a persistence query or model write. |
| Import without offset | Accepted only when the import contract declares the source timezone. For academic schedule imports, the source timezone is explicitly `Asia/Jakarta`; otherwise reject for clarification rather than guessing. |
| API/integration with offset | Preserve the supplied instant. Parse the offset-aware value, then convert to `Asia/Jakarta` only for local-day/business presentation and boundary calculations. |
| API/integration without offset | Reject or require an explicit source timezone. Never infer from a PostgreSQL session default. |
| Recurring academic schedule | Rule-local times are `Asia/Jakarta` business times. Each generated occurrence is normalized to an offset-aware instant before persistence. |
| Persistence | `timestamptz` stores an absolute instant. It does not permanently retain an `UTC` or `Asia/Jakarta` label. No semantic timezone is attached to the stored value. |
| PostgreSQL session | Set an explicit UTC technical/session baseline for application connections where the framework supports it. This controls rendering/interpretation of unqualified SQL timestamps, but does not replace application normalization. |
| Persistence input guard | No naive timestamp string may cross a model/query persistence boundary. Domain code must receive an aware `DateTimeInterface`/Carbon value or a value plus explicit source timezone. |
| Local-day query | Construct `startOfDay` in `Asia/Jakarta`, construct the next local day in `Asia/Jakarta`, convert both to instants, and query `[start, nextStart)` with start inclusive and next-day exclusive semantics. |
| Today | “Today” means the current calendar date in `Asia/Jakarta`, not the server/UTC calendar date. |
| In progress | A non-cancelled/non-rescheduled planned or confirmed session is `IN_PROGRESS` when its aware start instant is `<=` the aware business clock and its aware end instant is `>` that clock. |
| Overdue unfinished | A planned or confirmed session is `OVERDUE_UNFINISHED` when its aware end instant is `<=` the aware business clock and it has not reached the completed/finalized condition. |
| Completed | Canonical completed status remains authoritative; operational display must not infer completion merely from elapsed time. |
| Cancelled/rescheduled | Canonical raw status takes precedence and is excluded from active operational counts as already implemented. |
| Display/report | User-facing Academic screens, exports, and reports render business timestamps in `Asia/Jakarta`. Machine interfaces and technical diagnostics may use ISO-8601 with an explicit offset or UTC `Z`. |
| Audit/event timestamps | Preserve absolute instants. Human audit views localize to `Asia/Jakarta`; serialized evidence includes an explicit offset. |

## Runtime contract

- **Web:** normalize request date/time inputs before model/query use; derive
  request-period boundaries in `Asia/Jakarta`.
- **Queue/jobs:** carry instants or an explicit timezone-bearing value in the
  payload; do not rely on worker host timezone.
- **CLI/scheduler:** set the application timezone explicitly and normalize
  recurrence-generated values before persistence.
- **PostgreSQL:** use explicit session baseline, but treat it as a technical
  safeguard rather than the business interpretation authority.
- **Tests:** use `Carbon` values with `Asia/Jakarta` or explicit `+07:00` for
  business timestamps; include UTC-adjacent boundary cases and assert query
  instants, not driver-specific display strings.
- **Local development:** document the disposable PostgreSQL session baseline
  and require explicit offset-aware fixtures.
- **CI:** use the same technical session baseline as application CI and retain
  the fail-closed disposable database guard.
- **Integrations:** require offsets or an explicit source timezone; reject
  ambiguous timestamps.

## Five TZ-C implications

The following are implications if Option A is accepted. They do not, by
themselves, convert the R3 classification from TZ-C to TZ-A or TZ-B.

| R3 failure | Expected fixture representation | Expected query boundary/state rule | Source compatibility and replay requirement |
|---|---|---|---|
| Dashboard due/finalized/missing counts, `AcademicRoleDashboardServiceTest` line 235 | Persist all session and attendance times as `Carbon::parse(value, 'Asia/Jakarta')` or explicit `+07:00`; avoid bare strings | Period and Today windows are local Jakarta day converted to aware instants; compare with one aware clock | Current service has the intended comparison shape but uses global `Carbon::now()` in dashboard paths. Replay after fixture normalization is required before calling source defective. |
| Dashboard upcoming count, line 600 | The future session must be an aware Jakarta local time converted to an instant | `planned_start_at > aware business clock` means upcoming | Likely compatible, but current caller boundaries and global clock need targeted replay. |
| Wali joint due count, line 618 | Joint session and test clock use explicit Jakarta offsets | Same due/in-progress boundary; preserve scope-group inclusion independently | Joint scope logic must remain unchanged. Replay must separate timezone result from class-scope result. |
| Today open-state list, `AcademicTodaySessionServiceTest` line 40 | All three schedule intervals use explicit Jakarta offsets; the injected `now` carries the same zone/instant | Local day `[startOfDay, nextStart)` and start/end comparisons use aware instants | Service explicitly localizes the clock; replay must verify hydrated model casts and DB session rendering. |
| Today completed/overdue count, line 53 | Completed and planned intervals use explicit Jakarta offsets | End `<=` aware clock yields overdue only for non-completed raw status | Current state mapper is semantically clear; replay determines whether the defect is fixture/storage handling or hydration/application logic. |

The preferred next task may fix only test representation first, then replay the
same service behavior. Any remaining mismatch becomes a separate, evidence-based
TZ-B Academic application defect; no application change is pre-authorized here.

## Historical compatibility and data risk

Existing persisted pilot timestamps cannot be assumed correct solely because
the column is `timestamptz`. A bare local timestamp written while a connection
used UTC can represent a different instant from the same bare value written
while a connection used Asia/Jakarta. The repository does not provide enough
lineage to prove the timezone of every historical write.

Therefore:

- no historical rewrite or mass correction is authorized;
- a later read-only timestamp lineage/reconciliation audit should sample
  affected pilot rows, compare source/import provenance, and quantify ambiguity;
- any correction requires separate governance, evidence, backup/rollback, and
  explicit business approval;
- reports must not silently “correct” old values during this D1 or the next
  test-remediation task.

## Exact future implementation scope

### Contract enforcement candidate

- `application/web/config/database.php` — only if the accepted design requires
  an explicit PostgreSQL session timezone initialization.
- A narrowly scoped temporal normalization helper/value object in the Academic
  boundary, if existing Laravel parsing cannot enforce the input contract.
- `application/web/app/Domains/Academic/Services/AcademicTodaySessionService.php`
  — only if replay proves a source defect after aware fixtures/boundaries.
- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
  — only to unify injected/aware clock and local-day boundaries if replay
  proves it necessary.

### Test scope

- `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- focused timezone boundary/driver hydration tests using disposable PostgreSQL
  only; no SQLite fallback for these semantics.

### Separate R3 non-timezone remediation

The already diagnosed fixture/assertion files for `secret_last4`, audit
`occurred_at`, JSONB predicates, exclusion-safe fixtures, and closed status
vocabulary remain separate and must not be mixed into the timezone authority
decision.

### Security debt

`CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY` remains a separate later workflow
remediation. D1 does not change CI secrets or workflow behavior.

## Protected zones

- Applied migrations and existing schema are immutable.
- No pilot/staging/production data rewrite or migration.
- No weakening PostgreSQL constraints or changing `timestampTz` to preserve
  ambiguous fixtures.
- No unrelated report/KPI/attendance semantic change.
- No provider/OpenAI or public Academic AI activation.
- Public Academic AI remains `OFF`.

## Regression and UAT plan after owner approval

1. Add the accepted contract to the authoritative governance source.
2. Add focused PostgreSQL tests for `Asia/Jakarta` local-day boundaries around
   midnight, start/end equality, UTC-rendered hydration, queue/CLI execution,
   and offset-bearing integrations.
3. Convert the five TZ-C fixtures to explicit aware values and replay them.
4. If failures remain, classify the exact source defect before any patch.
5. Run focused Today/dashboard suites, Shared Core/Academic regression, the
   database identity guard, migration-from-zero, and full PostgreSQL 18.6 CI.
6. UAT: verify Indonesian local date, upcoming/in-progress/overdue states,
   joint-session scope, exports, audit display, queue execution, and CLI output.
7. Record all results in a change manifest and keep any unresolved failure
   explicit.

## Rollback design

D1 has no runtime or data change to roll back. For the later implementation:

- revert the single implementation commit if focused and full regression fails;
- do not reverse or edit applied migrations;
- do not rewrite historical timestamps as rollback;
- restore only from a verified backup if a separately authorized data correction
  ever occurs;
- retain the decision artifact and failed evidence for auditability.

## Owner decision gate

Option A is the recommended design candidate because it aligns with the current
application timezone, avoids hidden PostgreSQL session dependencies, preserves
absolute instants, and makes local-day semantics explicit. This is not a
self-ratification. Option B or C remains available to the Project Owner after
ChatGPT review.

`RECOMMENDED_OPTION = A`

`OWNER_DECISION_STATUS = PENDING_CHATGPT_AND_PROJECT_OWNER_AUDIT`

`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`
