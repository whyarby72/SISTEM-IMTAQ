# Canonical Pre-SOC Implementation Baseline v1.0

This governance artifact is established by SOC-I0C on 2026-09-19. It is the durable baseline after SNAP-G1, Migration A, and Migration B, before Session Occurrence implementation.

## 1. Purpose

Freeze the verified source, schema, aggregate data, recovery, and authorization boundary before SOC work.

## 2. Accepted Predecessors

SNAP-G1, SOC-I0R, MIG-A1, and MIG-B1 are accepted. Their Change Manifest hashes were verified: SNAP-G1 `cb3a50269aa4c581d8b6b6635794eea1fcc0a66e7b346e1db83b4805c2f21844`; SOC-I0R `07971a58b8e74b575f8547c88f41d5541c79a4d6ed03635734923db79cdee1fc`; MIG-A1 `cdaaf71b822f6110798bba33c690e3e685b979b0b5eea3d51ba9dbb2d4aa8767`; MIG-B1 `86e7521f1dccf56196941f618ae70db4f11826854f247b790af1a6ba7f6040d4`.

## 3. Application Source Baseline

Current application source matches the SNAP-G1 source manifest, with 0 unexpected changes. A filtered 450-file snapshot and manifest are retained in the SOC-I0C recovery directory. Snapshot manifest SHA-256: `0d043363f36c9c0c8b5d9916e065c5e76fc1b2fbfd4360d5edbbf5615b453fdb`.

## 4. PostgreSQL Identity

Verified pilot: PostgreSQL 18.6 at `127.0.0.1:5432`, database `imtaq`, timezone `Asia/Jakarta`.

## 5. Migration State

41 repository migrations are applied; 0 are pending.

## 6. Controlled Vocabulary State

Migration A has 9 expected installed and validated CHECK constraints. No constraint was removed or invalidated.

## 7. Semantic Foundation State

Migration B is infrastructure-only and complete. Both foundation tables exist and are empty. Eligibility columns exist and remain NULL for all current participant rows. No semantic authority is activated.

## 8. Participant Snapshot Guard

SNAP-G1 lazy/idempotent participant snapshot guard is present. Eager generation is absent. Future HELD behavior is documented but not implemented.

## 9. Current Business Aggregate Snapshot

Sessions 1,270; participants 26,261; student attendance 1,414; teacher participation 82; schedule changes 162; joint sessions 309; monthly summaries 5; monthly snapshots 84; import batches 2; import lineages 89. Detailed aggregate evidence is retained in the recovery checkpoint.

## 10. Legacy Session State

Legacy status vocabulary remains PLANNED, CONFIRMED, COMPLETED, CANCELLED, RESCHEDULED. No HELD status exists. COMPLETED has not been automatically mapped to HELD.

## 11. Recovery Artifacts

SOC-I0C recovery directory contains the fully migrated logical backup, schema dump, filtered source snapshot, source manifest, aggregate snapshot, migration state, constraint state, foundation state, and validated recovery hash manifest.

## 12. Cutover Boundary

Occurrence transactions are created only after separately authorized SOC implementation cutover. No historical ClassSession backfill is authorized.

## 13. Open Governance Items

MD-02 non-eligible authority remains OPEN; AUD-01 legacy import provenance remains OPEN_NONBLOCKING; source authority precedence and class lineage population are NOT ACTIVATED; Session Occurrence implementation is NOT STARTED; full attendance canonicalization is NO; AI gate is CLOSED.

## 14. Implementation Authorization State

`SOC_IMPLEMENTATION_READINESS = READY_FOR_IMPLEMENTATION_PLANNING`. `SOC_IMPLEMENTATION_AUTHORIZED = NO`. This baseline is evidence and does not authorize source mutation.

## 15. Next Atomic Phase

Return to ChatGPT for SOC-I0C audit. Any implementation requires a separate explicit authorization and recovery gate.
