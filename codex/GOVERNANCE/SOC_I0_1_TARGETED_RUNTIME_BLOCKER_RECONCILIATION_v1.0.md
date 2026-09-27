# SOC-I0.1 Targeted Runtime Blocker Reconciliation v1.0

Date: 2026-09-19  
Mode: read-only runtime reconciliation; no implementation authorization

## 1. Purpose

Reconcile the two unapplied migrations and targeted SOC-CAS runtime ambiguities without changing source, schema, or business data.

## 2. Authority Verification

Policy v1.1, SOC-CAS v1.0, and CIBS v1.0 hashes remained valid. The SOC-I0 recovery checksum manifest validated all 389 listed artifacts. No SOC-I0 recovery artifact was changed.

## 3. SOC-I0 Runtime Continuity

Database identity remained `imtaq|public|127.0.0.1/32|5432|Asia/Jakarta`; fingerprint remained `bc1f8e600fa3f7bc7cd14eca799025043527983b4be6d098c13708ef755912b2`. PostgreSQL runtime continuity: `PASS`.

## 4. Migration State Continuity

Repository migration files remained `41`; database applied migrations remained `39`; pending migrations remained exactly the expected two. Continuity: `PASS`.

## 5. Pending Migration A — Exact Effect

Migration A adds nine named PostgreSQL checks across `class_sessions`, `session_teacher_participations`, and `student_attendance`. It changes no columns, performs no backfill, depends on existing Academic tables, can take table-level locks during `ALTER TABLE`, and its `down()` drops only those checks. It is not idempotent on repeat `up()` because it does not use `IF NOT EXISTS`.

## 6. Pending Migration A — Data Preflight

All nine intended controlled-vocabulary rules have `0` current violations. Nullable status fields preserve the source NULL allowances. No same-name `chk_*` constraints currently exist. Data is compatible; decision: `SAFE_TO_APPLY_BEFORE_SOC`. No constraint was created.

## 7. Pending Migration B — Exact Effect

Migration B creates `attendance_source_certifications` and `class_lineage_mappings`, with UUID keys, user/class foreign keys, indexes, status/evidence fields, and timestamps. It adds nullable `eligibility_status` and `non_eligible_reason` plus an index to `session_student_participants`. It contains no data-copy/backfill logic. Its `down()` removes the new tables and participant columns/index.

## 8. Pending Migration B — Schema/Data Preflight

Both target tables are absent. The three referenced tables exist and four required referenced columns were verified. No equivalent structures, extension requirement, or target collision was found. Data backfill rows: `0_BY_SOURCE_CODE`. Schema/data decision: `SAFE_TO_APPLY_BEFORE_SOC`; operational disposition remains blocked pending management/migration authorization.

## 9. Migration Dependency Analysis

Occurrence persistence does not require Migration A as a schema dependency, but A should be resolved first to harden existing vocabulary. Migration B is optional parallel governance infrastructure and is not a direct prerequisite for the minimal additive occurrence fact; source-authority integration is only a partial dependency.

## 10. Substitute Primary-Lineage Reconciliation

There are `18` substitute sessions. All `18` retain a PRIMARY `SessionTeacherParticipation` row. Outside-row lineage: `0`; incomplete: `0`; unresolved: `0`. PRIMARY identity is therefore recoverable directly from the session participation table; no teacher names were exported.

## 11. Substitution 19-vs-18 Reconciliation

There are `19` substitution ScheduleChange rows across `18` distinct sessions. Exactly one session has two applied, reason-bearing substitution events. Classification: `MULTIPLE_HISTORICAL_SUBSTITUTION_EVENTS_ON_ONE_SESSION`, not duplicate session participation and not unresolved.

## 12. Cancellation 91 / 65 / 26 Reconciliation

All `91` cancelled sessions have a linked ScheduleChange: `65` with CANCELLATION and `26` with SCHEDULE_REVISION. No session has both, another type, or no ScheduleChange. All cohorts have a non-empty reason. The earlier “26 without reason” result was a query-scope artifact that looked only for a CANCELLATION-type link; those 26 are revision-backed cancellations with preserved reasons.

## 13. Cancellation Cohort Classification

- `65` explicit CANCELLATION: `CURRENT_CANONICAL_COMPATIBLE`.
- `26` SCHEDULE_REVISION-backed cancellation: `LEGACY_VALID_LINEAGE` pending future canonical projection review.
- Both/other/no-lineage/no-reason cohorts: `0`.

No underlying record was modified.

## 14. Missing Participant Snapshot Reconciliation

The three opaque references are all `PAST`, `CANCELLED`, `SINGLE`, with no student attendance, no teacher participation, and a ScheduleChange. All were created after the snapshot-feature boundary used for this audit. Classification: `CURRENT_DATA_QUALITY_BLOCKER=3`; repair required before trusting a universal participant-snapshot invariant, but no attendance opportunity is inferred for these cancelled sessions.

## 15. ScheduleChange Actor-Lineage Reconciliation

All `52` IMPORT_CORRECTION rows lack the three human actor fields; `0` non-import rows do. The correlation is exact, but `schedule_changes` has no direct import-batch/import-lineage foreign key or provenance column. Therefore the gap is structurally correlated with legacy import correction, but not fully proven as machine-linked import provenance. Human audit lineage remains unresolved.

## 16. Past PLANNED Segmentation

Past PLANNED total: `381`; with student attendance `0`; with teacher participation `3`; with ScheduleChange `14`; without participant snapshot `0`; joint `128`; single `253`. By month: July `72`, August `192`, September `117`. No occurrence outcome was inferred.

## 17. Past PLANNED Cutover Classification

All `381` can remain outside the initial transaction-created-after-authorized-SOC-I1 cutover as legacy workflow rows: `SAFE_TO_LEAVE_AS_LEGACY=381`; review `0`; repair `0`; unresolved `0`. The 14 ScheduleChange-linked rows remain review signals, not HELD/CANCELLED evidence.

## 18. Transitional Denominator Runtime Check

Runtime matches current transitional behavior: planned-with-attendance `0`, cancelled-with-attendance `0`, completed-with-attendance `82`. Result: `YES`. This does not make `COMPLETED` canonical `HELD`.

## 19. Source Authority Dependency

Both source-authority tables are absent because Migration B is unapplied. They are not required to create the minimal occurrence fact, but are a partial prerequisite for a governed source-authority integration and class-lineage reconciliation. No MD-02 decision was resolved.

## 20. Migration Order Matrix

| Change | Current state | Preflight | Dependency | Risk | Recommended order | Decision |
|---|---|---|---|---|---|---|
| Migration A | Unapplied | 0 violations | Existing tables only | Lock/idempotency | First after restore rehearsal | Apply before SOC |
| Migration B | Unapplied | Targets absent; FKs compatible | Governance/source authority | New tables + rollback data loss | Second, separately gated | Blocked pending management decision |
| Future occurrence persistence | Not implemented | Not profiled here | Independent additive fact | High semantic/cutover | After A/B review and checkpoint | Not authorized |
| Future occurrence indexes/constraints | Not implemented | Not created | Depends on occurrence schema | Medium/high locking | With occurrence migration | Not authorized |
| Future RBAC/source changes | Not implemented | Not changed | Policy and service gate | High authorization | After schema checkpoint | Not authorized |

## 21. Pending Migration Dispositions

- Controlled vocabulary: `APPLY_BEFORE_SOC`.
- Semantic foundation: `BLOCKED_PENDING_MANAGEMENT_DECISION`.

These are recommendations only; no migration executed.

## 22. SOC-CAS Ambiguity Resolution

Contradictions: `0`. Targeted ambiguities before: `7`; resolved: `6`; remaining: `1` (human actor provenance for legacy IMPORT_CORRECTION rows). The remaining item is an audit/data-governance blocker, not evidence to invent occurrence state.

## 23. Implementation Blocker Register

Open before SOC-I1: `MIG-01` pending controlled-vocabulary migration, `MIG-02` pending semantic foundation migration, `SNAP-01` three post-feature missing snapshots, and `AUD-01` unproven import-to-ScheduleChange provenance. Resolved items: substitute PRIMARY lineage, 19-vs-18 count, and cancellation 26 cohort explanation. Full register is in the recovery checkpoint.

## 24. SOC-I1 Readiness Decision

`SOC_I1_READINESS=BLOCKED`. SOC-I1 is not authorized. A disposable restore rehearsal and targeted blocker review must precede any implementation planning.

## 25. Next Gate

`TARGETED_BLOCKER_RESOLUTION_REVIEW`, followed by `SOC-I0R_DISPOSABLE_RESTORE_REHEARSAL` only after blocker review accepts the evidence.
