# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-TZ-D1A-OPTION-A-OWNER-APPROVAL`  
**State:** `COMPLETED / OWNER_DECISION_RECORDED`  
**Current phase:** `TIMEZONE AUTHORITY / OWNER RATIFICATION`  
**Branch:** `chore/foundation-tz-d1a-option-a-approval`  
**State-basis:** `14c63b854fa17d8cc1f757104fa1d79438cfd9a3`

## Decision

The Project Owner explicitly selected:

`OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL_SESSION / TIMEZONE_AWARE_BOUNDARIES`

Canonical decision artifact:

`codex/DECISIONS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY-2026-09-28.md`

## Ratified contract

- Business/institutional timezone: `Asia/Jakarta`
- Human local schedule input: explicit Asia/Jakarta interpretation at boundary
- PostgreSQL `timestamptz`: absolute instant
- PostgreSQL/application technical session baseline: UTC
- Today/local-day/report/display semantics: Asia/Jakarta
- Tests: timezone-aware values or explicit offsets
- No migration edit
- No historical mass timestamp rewrite

## Next implementation gate

`FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`

R4 must first remediate test/config/workflow scope and replay the five prior
TZ-C failures. It must not modify Academic business services unless a residual
failure after aware-fixture replay proves an application defect and a separate
scope is authorized.

Public Academic AI remains OFF.
