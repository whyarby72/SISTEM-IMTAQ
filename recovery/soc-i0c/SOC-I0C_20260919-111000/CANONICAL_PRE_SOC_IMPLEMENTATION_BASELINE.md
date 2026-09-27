# Canonical Pre-SOC Implementation Baseline — SOC-I0C

## 1. Purpose

Durable post-migration checkpoint before Session Occurrence implementation. This baseline records current source, PostgreSQL schema, aggregate business state, recovery artifacts, and the explicit boundary that source mutation is not authorized by this checkpoint.

## 2. Accepted Predecessors

SNAP-G1, SOC-I0R, MIG-A1, and MIG-B1 are accepted. Predecessor manifest hashes are recorded in `codex/CHANGE_MANIFESTS/` and verified during this checkpoint.

## 3. Application Source Baseline

The current application source matches the accepted SNAP-G1 source manifest: 6/6 files, 0 unexpected changes. The durable source snapshot contains 450 files and excludes `.env`, vendor, node_modules, logs, runtime cache, secrets, and `.DS_Store`.

Source manifest SHA-256: `0d043363f36c9c0c8b5d9916e065c5e76fc1b2fbfd4360d5edbbf5615b453fdb`.

## 4. PostgreSQL Identity

Pilot identity is verified: PostgreSQL 18.6, `127.0.0.1:5432`, database `imtaq`, timezone `Asia/Jakarta`.

## 5. Migration State

Repository migrations: 41. Database applied: 41. Pending: 0.

## 6. Controlled Vocabulary State

Migration A has 9 expected controlled-vocabulary CHECK constraints. All 9 are installed and validated. Actual PostgreSQL definitions are in `MIGRATION_A_CONSTRAINTS.txt`.

## 7. Semantic Foundation State

Migration B infrastructure is present. `attendance_source_certifications` and `class_lineage_mappings` both exist and are empty. `eligibility_status` and `non_eligible_reason` exist on `session_student_participants`, are nullable with no default, and remain NULL for all existing rows. Business semantics are not activated.

## 8. Participant Snapshot Guard

The accepted lazy/idempotent `SessionParticipantSnapshotter` guard remains present. Eager snapshot-on-session-generation is not present. Future HELD snapshot requirements remain a contract only; HELD is not implemented.

## 9. Current Business Aggregate Snapshot

Current aggregate evidence is in `CURRENT_BUSINESS_AGGREGATE_SNAPSHOT.txt`: 1,270 sessions; 26,261 participants; 1,414 student attendance rows; 82 teacher participation rows; 162 schedule changes; 309 joint sessions; 5 monthly summaries; 84 monthly snapshots; 2 import batches; 89 import lineages.

## 10. Legacy Session State

Current status vocabulary remains PLANNED, CONFIRMED, COMPLETED, CANCELLED, RESCHEDULED. Current data has CANCELLED 91, COMPLETED 82, and PLANNED 1,097. No HELD status exists and COMPLETED was not auto-mapped to HELD. Canonical occurrence implementation is not started.

## 11. Recovery Artifacts

The post-migration logical backup and schema dump are in `post/`; the logical dump is SHA-256 `ebe3b43ac5d1dbf8687626aca7e57852cae28c3199f7a6abbf9543e67e48cbea`, schema dump is `51229358bbf5b610cb88b1178f568ac4af5e8320177d971ececa69920711e749`, and `pg_restore --list` is valid.

## 12. Cutover Boundary

`TRANSACTION_CREATED_AFTER_AUTHORIZED_SOC_IMPLEMENTATION_CUTOVER` remains the accepted strategy. No legacy ClassSession backfill is authorized. Historical occurrence backfill is NO; legacy COMPLETED auto-HELD is NO.

## 13. Open Governance Items

- `MD_02_NON_ELIGIBLE_AUTHORITY = OPEN`
- `AUD_01_LEGACY_IMPORT_PROVENANCE = OPEN_NONBLOCKING`
- Source authority precedence: NOT ACTIVATED
- Class lineage population: NOT ACTIVATED
- Session Occurrence implementation: NOT STARTED
- Full attendance canonicalization: NO
- AI gate: CLOSED

## 14. Implementation Authorization State

SOC implementation readiness is `READY_FOR_IMPLEMENTATION_PLANNING`. This checkpoint does not authorize source mutation. `SOC_IMPLEMENTATION_AUTHORIZED = NO`.

## 15. Next Atomic Phase

Return this baseline to ChatGPT for SOC-I0C audit. The next required gate is planning/authorization for a separately approved SOC implementation task. Stop here.
