# 11 — Historical Migration Contract

## Principle
Preserve what is known, preserve what is unknown, never manufacture historical precision.

## Migration flow
`Source Inventory → Raw Preservation → Profile → Staging → Identity Mapping → Value Mapping → Validation → Dry Run → Reconciliation → Human Approval → Import → Post-Import Reconciliation → Close Batch`

## Source file rules
- Retain original source file.
- Store filename, checksum, owner/source period, received_at.
- Do not clean source in place; transform in staging.

## Legacy granularity vocabulary
- SESSION_LEVEL
- DAILY_LEVEL
- MONTHLY_SUMMARY
- SEMESTER_FINAL
- SCHEDULE_RULE
- ROSTER_SNAPSHOT
- DOCUMENT_ONLY
- UNKNOWN

## Attendance migration
### Session-level source
May map to canonical class sessions/participants/attendance only when the source actually supports session identity/context.

### Daily source
Store/read through a legacy daily layer if required. Do not expand one daily status into several session attendance facts.

### Monthly summary
Store as legacy summary. Do not fabricate dates/sessions.

Known schedule alone is not proof a historical session occurred.

## Teacher attendance migration
Monthly/aggregate presence counts do not become session-level teacher participation attendance unless source evidence supports exact sessions.

## Semester grade migration
Final Student×Subject×Semester grade may map directly to canonical `semester_subject_grades` with `grade_source=IMPORTED` after identity/subject/semester validation.

Missing grade never becomes zero.

## Identity matching priority
1. Canonical Student_ID if present.
2. Verified institutional student code.
3. Exact verified NISN.
4. Exact verified NIS within valid scope.
5. Approved reusable legacy mapping.
6. Human review.

Name/fuzzy match may suggest candidates but must not auto-merge.

## Migration infrastructure requirements
- dry run creates no canonical rows
- every source row accounted for
- blocking vs warning error classification
- quarantine supported
- idempotent rerun
- canonical target traceable to source row/file/batch
- pre-acceptance rollback only under controlled dependency-safe conditions
- post-acceptance correction/enrichment uses a new audited batch or normal correction workflow

## Reconciliation equation
`Source Total = Imported + Rejected + Quarantined + Duplicate + Explicitly Excluded`

If totals do not reconcile, batch cannot close.

## Cutover
Go-live master/current-state migration and historical enrichment are separate workstreams. Do not maintain legacy spreadsheets as a permanent second Source of Truth after cutover.
