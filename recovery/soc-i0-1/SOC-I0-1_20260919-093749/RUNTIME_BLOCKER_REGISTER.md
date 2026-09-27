# SOC-I0.1 Runtime Blocker Register

| ID | Description | Severity | Evidence | Owner | Before SOC-I1? | Resolution |
|---|---|---|---|---|---|---|
| MIG-01 | Controlled-vocabulary migration is unapplied. | HIGH | 0 data violations; 9 constraints pending; DB has no matching checks. | Release/database gate | YES | MIGRATION |
| MIG-02 | Semantic foundation migration is unapplied; source-certification and class-lineage tables absent. | HIGH | Target tables absent; referenced schema compatible; no backfill in source. | Governance/database gate | YES | MIGRATION / POLICY |
| SUB-01 | PRIMARY lineage for substitute sessions. | RESOLVED | 18/18 substitute sessions have a PRIMARY participation row; outside/incomplete/unresolved 0. | Academic domain | NO | NONE |
| SUB-02 | 19 substitution changes vs 18 substitute sessions. | RESOLVED | 18 distinct sessions; one session has two applied, reason-bearing substitution events. | Academic audit | NO | NONE |
| CAN-01 | 26 cancellation reason ambiguity. | RESOLVED | 65 explicit CANCELLATION + 26 SCHEDULE_REVISION cohorts; both have reasons; no no-reason cohort. | Academic audit | NO | NONE |
| SNAP-01 | Three sessions lack participant snapshots. | HIGH | All are past CANCELLED, single, no student attendance, no teacher participation, with ScheduleChange; created after snapshot feature boundary. | Academic data quality | YES | DATA REVIEW |
| AUD-01 | 52 IMPORT_CORRECTION ScheduleChange rows lack human actor fields. | MEDIUM | Exactly 52/52 import-correction rows; 0 non-import rows; no direct import FK/lineage column. | Audit/import governance | YES | DATA / AUDIT |
| LEG-01 | 381 past PLANNED sessions. | LOW | 0 attendance, 3 teacher participation, 14 ScheduleChange, 128 joint, 253 single; no missing snapshots. | Academic operations | NO for initial cutover | POLICY / REVIEW |

Current implementation blockers requiring resolution before SOC-I1: `4` (`MIG-01`, `MIG-02`, `SNAP-01`, `AUD-01`).
