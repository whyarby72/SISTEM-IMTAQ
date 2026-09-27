# SNAP-01 Source-Path Analysis

## Evidence

All three rows are opaque references only. Each is a `SCHEDULED` ClassSession with a schedule rule, was later cancelled by one applied `SCHEDULE_REVISION`, has no participant snapshot, no teacher participation, no student attendance, and no import-lineage match. The source session creation dates precede the cancellation application timestamps. This supports a scheduled-generation followed by revision-cancellation path, not import corruption or a participant-snapshot transaction failure.

## Actual source path

`ClassSessionGenerator::generate()` creates the ClassSession and its class-session scope groups in one transaction, but does not call `SessionParticipantSnapshotter`. `ExtraSessionCreator::create()` and `RescheduleService::apply()` also create sessions without snapshotting. `SessionParticipantSnapshotter::snapshot()` is a separate, explicit transaction invoked by the attendance controller and the bulk snapshot controller. Its `firstOrCreate` operation is idempotent.

`ScheduleRuleRevisionService::revise()` selects qualifying planned sessions and directly updates them to `CANCELLED` before creating an applied `SCHEDULE_REVISION` ScheduleChange. `CancellationService` likewise only changes status and records a ScheduleChange; it neither creates nor deletes participant snapshots.

Therefore the three observed rows are valid cancelled-before-snapshot materialization cases. The source design also leaves a reachable future gap: a newly generated scheduled session can remain without a snapshot until an explicit snapshot action. That is a source-path risk for the future canonical cutover even though the three observed historical rows are not snapshot-creation failures.

## Required disposition

PARTICIPANT_SNAPSHOT_CREATION_OWNER = `SessionParticipantSnapshotter`

PARTICIPANT_SNAPSHOT_CREATION_TRIGGER = explicit `snapshotParticipants` or `bulkSnapshot` controller action; no automatic generator trigger

PARTICIPANT_SNAPSHOT_CREATION_TRANSACTIONAL = YES (snapshot operation); generation-to-snapshot coupling = NO

CANCELLATION_DELETES_PARTICIPANT_SNAPSHOT = NO

CANCELLATION_CAN_PRECEDE_SNAPSHOT_CREATION = YES / PATH-DEPENDENT (revision/archive/cancellation may cancel a planned session before explicit snapshot)

Per observed session: `CANCELLED_BEFORE_SNAPSHOT_MATERIALIZATION_VALID_PATH` = 3.
Observed legacy = 0; import = 0; creation failure = 0; observed failed active rows = 0; unresolved observed rows = 0.
The reachable source-path risk is recorded separately as an active pre-cutover design blocker, not misrepresented as historical creation failure.

## Future risk

`SNAP_01_FUTURE_SOC_RISK = HIGH`. A future scheduled session can exist without a participant snapshot because snapshotting is not coupled to all creation paths. Current code has no `HELD` state, but canonical occurrence implementation could otherwise receive an incomplete event; opportunity calculation and joint attribution would be empty/incomplete, and Wali workflow can reach the session page before roster materialization. A source invariant or equivalent precondition is required before SOC implementation.

`SNAP_01_REQUIRES_SOURCE_FIX_BEFORE_SOC = YES`.
`SNAP_01_DISPOSITION = SOURCE_FIX_REQUIRED_BEFORE_SOC`.

No source fix or historical repair was performed.
