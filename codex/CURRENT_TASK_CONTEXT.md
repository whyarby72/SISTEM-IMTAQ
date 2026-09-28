# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY`  
**State:** `COMPLETED / DECISION_DESIGN_ONLY / PASS`
**Current phase:** `CI / TIMEZONE & TIMESTAMP AUTHORITY`  
**Branch:** `chore/foundation-tz-d1-timezone-storage-authority`  
**State-basis:** `2d183ded78b6e3da4358ab499caad4d2beddea20`

## Accepted R3 outcome

`FOUNDATION-DB-R3 = CLOSED / ACCEPTED / DIAGNOSIS_ONLY / PASS`

Exact-current CI remains:
- run: `36402569265`
- exact CI head: `349ef67ac6e9e6a2d02a25e137084b00fe820c21`
- result: `16 failed, 15 passed, 490 warnings, 2171 assertions`

R3 classification:
- 7 TEST_FIXTURE
- 4 TEST_ASSERTION
- 5 CONTRACT_GAP / TZ-C

## Active authority gap

Five Today/dashboard failures cannot be safely remediated until the repository
defines one authoritative contract for:

- institutional/business timezone;
- naive local input interpretation;
- timestamptz persistence semantics;
- PostgreSQL session timezone;
- local-day query boundaries;
- operational-state comparison;
- display/report timezone.

## Required now

1. `codex/TASK_CONTEXTS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY.md`
2. R3 diagnostic
3. `application/web/config/app.php`
4. `application/web/config/database.php`
5. `ClassSession` model
6. Today/dashboard services
7. five TZ-C tests
8. PostgreSQL/schema/testing governance docs
9. targeted repository timestamp convention search
10. `PROJECT_STATE.json`
11. `NEXT_ACTION.md`

## Candidate recommendation

Evaluate:

`OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL / AWARE_BOUNDARIES`

Do not self-ratify it.

## Output

Create:

`codex/DECISIONS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY-2026-09-28.md`

Required closeout:

- `RECOMMENDED_OPTION = <A|B|C>`
- `OWNER_DECISION_STATUS = PENDING_CHATGPT_AND_PROJECT_OWNER_AUDIT`
- `IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`

## Boundary

DECISION/DESIGN ONLY.

Do not modify source, tests, config, workflow, migrations, schema, data,
dependencies, provider state, deployment, or main.

## Exit

Commit/push decision + state/evidence only.
STOP for ChatGPT audit.

## D1 closeout

Decision artifact created:
`codex/DECISIONS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY-2026-09-28.md`

`RECOMMENDED_OPTION = A` is a candidate recommendation only. It is not
self-ratified. `OWNER_DECISION_STATUS =
PENDING_CHATGPT_AND_PROJECT_OWNER_AUDIT` and
`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`.

No application source, test, configuration, workflow, migration, schema,
database, dependency, provider, deployment, or main-branch mutation was
performed.

**NEXT_ATOMIC_TASK:** `RETURN_TO_CHATGPT_FOR_FOUNDATION_TZ_D1_AUDIT`
