# FOUNDATION-TZ-D1 — Timezone & Timestamp Storage Authority

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** CROSS_DOMAIN_TIME_AUTHORITY_DECISION_DESIGN  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** chore/foundation-tz-d1-timezone-storage-authority  
**State-basis:** 861ad624086141b1b1fedd3073ee3b886ce46ac9

## Trigger

FOUNDATION-DB-R3 is CLOSED / ACCEPTED.

Exact-current PostgreSQL foundation evidence remains:

- GitHub Actions run: `36402569265`
- exact CI head: `349ef67ac6e9e6a2d02a25e137084b00fe820c21`
- result: `16 failed, 15 passed, 490 warnings, 2171 assertions`

R3 classified:
- 7 failures as `TEST_FIXTURE`;
- 4 failures as `TEST_ASSERTION`;
- 5 time-sensitive failures as `CONTRACT_GAP / TZ-C`.

The five TZ-C failures affect Today/dashboard session-state semantics and cannot
be remediated safely until the repository has one authoritative timezone,
timestamp-input, persistence, query-boundary, and display contract.

## Domain and process objective

**Domain:** cross-domain temporal semantics, currently blocking Academic CI.  
**Process owner requiring authority:** Project Owner / Architecture Governance.  
**Primary consumers:** Academic scheduling, attendance, dashboard, reporting,
future cross-domain activities, audit timestamps, and integration boundaries.  
**Source of truth:** canonical timestamp contract recorded in repository
governance/decision artifacts; database values remain transactional evidence.  
**Smallest affected grain:** one timestamped business event/session field plus
its timezone/instant interpretation.  
**Validator/approver:** Project Owner after ChatGPT audit.  
**Audit requirement:** decision artifact + Git history; no silent semantic
change.  
**Privacy/security:** no PII or secret handling change.  
**AI need:** none.

## Goal

Produce a decision-ready timezone/storage contract and exact implementation
consequences. D1 is decision/design only.

Do not implement application, test, DB config, workflow, migration, or data
changes.

## Current authoritative evidence

Inspect and reconcile at minimum:

1. R3 diagnostic:
   `codex/DIAGNOSTICS/FOUNDATION-DB-R3-POSTGRES-REGRESSION-DIAGNOSIS-2026-09-28.md`
2. `application/web/config/app.php`
3. `application/web/config/database.php`
4. `application/web/app/Domains/Academic/Models/ClassSession.php`
5. `application/web/app/Domains/Academic/Services/AcademicTodaySessionService.php`
6. `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
7. the five TZ-C failing tests from exact run `36402569265`
8. canonical PostgreSQL/schema docs and testing strategy
9. any repository-wide timestamp conventions found by targeted search.

Do not use general assumptions to override repository evidence. Distinguish
PostgreSQL behavior from the project policy that must be chosen.

## Decision options

### OPTION A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL / AWARE_BOUNDARIES

Candidate recommendation.

Contract:

- institutional/business operating timezone:
  `Asia/Jakarta`;
- human-entered schedule times without an explicit offset are interpreted at
  the application/domain boundary as `Asia/Jakarta`, never by relying on the
  database session timezone;
- persisted `timestamptz` represents an absolute instant; no semantic meaning
  is attached to a storage/session display timezone;
- PostgreSQL connection/session timezone baseline is explicitly `UTC` where
  application configuration supports an explicit session setting;
- application/domain comparisons use timezone-aware Carbon/DateTime values;
- local-day, Today, schedule-window, reporting-day, and display semantics are
  evaluated/rendered in `Asia/Jakarta`;
- tests must use timezone-aware values or explicit offsets (for example
  `+07:00`) for business timestamps;
- integrations carrying offsets preserve the supplied instant and convert to
  `Asia/Jakarta` only for institutional local semantics;
- naive timestamps are not allowed to cross persistence boundaries without
  explicit interpretation.

Important precision:
PostgreSQL `timestamptz` must be described as storing an instant, not as a
timestamp value that permanently retains an `UTC` or `Asia/Jakarta` zone.

### OPTION B — ASIA_JAKARTA_APPLICATION_AND_DB_SESSION

Contract:

- business timezone `Asia/Jakarta`;
- PostgreSQL session timezone also `Asia/Jakarta`;
- naive DB-bound timestamps are interpreted according to that session timezone;
- tests and deployment must guarantee the same DB session timezone.

Assess portability, hidden-environment dependency, worker/queue/CLI behavior,
connection-pool behavior, integration behavior, and production failure modes.

### OPTION C — HOLD

Use HOLD if the repository or operational authority is insufficient to freeze a
safe time contract.

## Required analysis

D1 must explicitly decide or recommend policy for all of these:

### Business clock
- institutional operating timezone;
- local calendar date meaning;
- "today" meaning;
- daylight-saving assumption relevant to Asia/Jakarta.

### Input contract
- UI/user-entered local date/time;
- import files without offset;
- API/integration timestamp with offset;
- API/integration timestamp without offset;
- scheduled recurring academic times.

### Persistence contract
- `timestampTz` semantics;
- UTC/session timezone baseline;
- prohibition or handling of naive persistence-bound timestamp strings;
- whether any existing columns require a schema change (expected answer:
  normally no; prove it).

### Query contract
- local-day windows;
- start-inclusive / next-day-exclusive boundaries;
- future/in-progress/overdue comparison;
- date filtering against `timestamptz`;
- conversion point between local calendar boundaries and absolute instants.

### Display/reporting contract
- user-facing timezone;
- exports/reports;
- audit/event timestamps;
- whether any technical timestamps should remain UTC in machine interfaces.

### Runtime contract
- web requests;
- queues/jobs;
- CLI/scheduled tasks;
- tests;
- PostgreSQL session;
- local development;
- CI.

### Backward compatibility
Determine whether existing persisted pilot timestamps can be assumed correct.
Do not rewrite data.

If historical values may have been written under ambiguous timezone semantics,
D1 must identify a separate read-only reconciliation/audit need rather than
authorizing mass correction.

## Five TZ-C failures

For each of R3 failures #5–#9, D1 must state what the selected/recommended
contract would imply:

- expected fixture representation;
- expected query boundary;
- expected operational-state comparison;
- whether current application source is likely compatible or requires a later
  targeted replay before classifying TZ-A vs TZ-B.

D1 must not convert TZ-C to TZ-A or TZ-B solely by policy declaration.
The classification changes only after implementation/replay evidence.

## Implementation impact map

Produce exact candidate write scope for the later remediation task.

Separate:

### Contract-enforcement files
Potentially:
- `application/web/config/database.php`
- narrowly scoped temporal helper/value object if needed
- Academic Today/dashboard service files only if replay proves source defect
- test fixtures/assertions
- CI env/session-timezone setup if needed

### Test-only R3 remediation
The already diagnosed 11 non-timezone failures remain separate, bounded
corrections.

### Security debt
`CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY` remains part of later remediation,
not D1.

### Protected zones
- applied migrations immutable;
- no production/pilot data rewrite;
- no weakening PostgreSQL constraints;
- no unrelated reporting/KPI semantic change;
- no provider/OpenAI activation;
- public Academic AI remains OFF.

## Candidate recommendation to evaluate

D1 should evaluate, not automatically ratify, this candidate:

`OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL / AWARE_BOUNDARIES`

Rationale to verify:
- aligns with current `config('app.timezone') = Asia/Jakarta`;
- avoids dependence on PostgreSQL runner/session defaults;
- preserves absolute instants in `timestamptz`;
- makes local-day Academic semantics explicit;
- is safer across web/queue/CLI/CI/integration contexts;
- does not require weakening or editing historical migrations.

If evidence contradicts any rationale, record it and adjust recommendation.

## Required artifact

Create:

`codex/DECISIONS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY-2026-09-28.md`

It must contain:

- problem/root cause;
- repository evidence;
- A/B/C comparison;
- recommended option;
- full timestamp contract matrix:
  input → normalize → persist → query → display;
- five TZ-C implications;
- backward-compatibility/historical-data risk;
- exact future implementation scope;
- protected zones;
- regression/UAT plan;
- rollback;
- explicit owner-decision gate;
- explicit implementation authorization state.

Required closeout fields:

`RECOMMENDED_OPTION = <A|B|C>`

`OWNER_DECISION_STATUS = PENDING_CHATGPT_AND_PROJECT_OWNER_AUDIT`

`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`

## Allowed writes

Only:
- the D1 decision artifact;
- `PROJECT_STATE.json`;
- `TEST_MATRIX.csv`;
- `EVIDENCE_INDEX.json`;
- `codex/CURRENT_TASK_CONTEXT.md`;
- `NEXT_ACTION.md`.

## Forbidden

Do not modify:
- application source;
- tests/fixtures;
- `config/app.php`;
- `config/database.php`;
- workflow implementation;
- PHPUnit/TestCase;
- migrations;
- schema/constraints;
- persistent data;
- Composer/PHP/dependencies;
- OpenAI/provider state;
- deployment;
- main branch.

No database writes or migrations.

## Acceptance criteria

- FTZ-D1-AC-01 exact R3 baseline and CI evidence recorded.
- FTZ-D1-AC-02 business timezone authority analyzed.
- FTZ-D1-AC-03 input/persistence/query/display contract complete.
- FTZ-D1-AC-04 PostgreSQL `timestamptz` semantics stated precisely.
- FTZ-D1-AC-05 A/B/C comparison complete.
- FTZ-D1-AC-06 all five TZ-C implications mapped.
- FTZ-D1-AC-07 backward-compatibility/historical timestamp risk addressed.
- FTZ-D1-AC-08 exact later implementation scope and regression plan defined.
- FTZ-D1-AC-09 no implementation/data mutation performed.
- FTZ-D1-AC-10 owner decision remains explicit and not self-authorized.

## Expected routing after audit

If Option A or B is accepted by the Project Owner, route a separately authorized
implementation/remediation task.

Preferred next ID if the time contract is ratified:

`FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`

If authority remains insufficient:
`HOLD_TIMEZONE_STORAGE_AUTHORITY`

Then STOP for ChatGPT audit.
