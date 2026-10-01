# First-Day Wali Controlled Attendance UAT

**Task:** `ACADEMIC-WALI-PILOT-FIRST-DAY-CONTROLLED-ATTENDANCE-UAT`
**Mode:** `CONTROLLED_LIVE_PILOT_OPERATIONAL_TRANSACTION`
**Status:** `HOLD / HUMAN_OBSERVATION_REQUIRED`
**Repository HEAD:** `f3cdcfa8db5bb164a2634b01741781a5e2bc536b`
**Executable CI authority:** `36936007221 = SUCCESS` on `e2b44cecbe1b2eb90b009b3f3464c7a6565dda42`

## Repository gate

The requested branch was clean and local/remote parity matched at the exact
starting HEAD. The delta after the cited green executable CI was limited to
`EVIDENCE_INDEX.json` and `codex/CURRENT_TASK_CONTEXT.md`; no application
source, tests, migration, schema, or runtime configuration drift was found.

## PILOT read-only preflight

One aggregate-only preflight transaction proved:

| Guard | Result |
|---|---|
| database | `imtaq` |
| PostgreSQL | `18.6` |
| `transaction_read_only` | `on` |
| session timezone | `Asia/Jakarta` |
| transaction end | `ROLLBACK` |
| database write | `NONE` |

## Selected candidate

Exactly one candidate was selected for the human-operated UAT checkpoint:

| Field | Aggregate/sanitized value |
|---|---|
| class | `IMTAQ-2026-1` |
| business date/time | `2026-10-03 08:00 Asia/Jakarta` |
| scope | standalone operational class session |
| lifecycle | `PLANNED` |
| participant count | `20` required participants |
| subject reference | present |
| Wali authority | PASS |
| PRIMARY obligation | PASS; exactly one canonical expected PRIMARY |
| existing student attendance facts | `0` |
| existing teacher attendance facts | `0` |
| blocking lock | `0` |

The candidate is reportable, not cancelled, not finalized/validated, and has
the required participant snapshot. No raw IDs or PII are recorded here.

## Human observation gate

The authorized Wali/operator has not yet supplied or entered the actual
observed student and teacher attendance facts. The system must not infer
`PRESENT`, `ABSENT`, or any other state from missing input. Therefore:

- draft write: **not executed**;
- teacher attendance write: **not executed**;
- finalization: **not executed**;
- post-write verification: **not applicable**;
- database write this task: **NONE**.

Resume only through the normal Wali application path after the authorized
human operator reviews and enters the real observations. Do not execute a
second session, create synthetic attendance, or bypass the application.

## Closeout

`SAFE_TO_CLOSE = YES` for this checkpoint.

Next action: authorized human observation and application-path entry for the
single candidate above; then independently reverify the draft/finalization
roundtrip before any finalization decision.
