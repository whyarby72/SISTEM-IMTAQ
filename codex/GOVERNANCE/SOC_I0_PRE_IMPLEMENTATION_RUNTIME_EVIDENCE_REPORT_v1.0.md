# SOC-I0 Runtime Evidence Report v1.0

Task: SOC-I0 — Pre-Implementation Runtime Evidence & Recovery Gate  
Date: 2026-09-19  
Mode: read-only runtime evidence and recovery checkpoint

## 1. Purpose

Verify the actual local Academic pilot PostgreSQL runtime, preserve a rollback-capable application/database checkpoint, and profile aggregate session evidence before any Session Occurrence implementation. No implementation is authorized by this report.

## 2. Authority Verification

- Policy v1.1: valid; SHA-256 `ecd1f08fe7d6772ee4f10f7a9e35db4a7c6cb66b2ba57dc5aff5622033caddea`.
- SOC-CAS spec: valid; SHA-256 `381c5a3dd3d154cbfa10f4827ac7415cdbd19388a3253d1bacfd57b90ca4e23b`.
- SOC-CAS manifest: valid; SHA-256 `00ba3a1b4b5985285318da2be8beb39901289d369a44a01a0b49ed27014cf516`.
- CIBS baseline: valid; SHA-256 `37a9f4b4866fd70b4b1de4a3fec29503dc9368198a5437171cdb80f95b9f4d85`.

## 3. Application Runtime Database Configuration

`APP_DATABASE_DRIVER=pgsql`; connection `pgsql`; host class `LOOPBACK`; port `5432`; database identifier `imtaq`; environment `local`; config cache absent; SQLite is not the resolved runtime.

## 4. PostgreSQL Identity

PostgreSQL 18.6, address `127.0.0.1`, port `5432`, schema `public`, timezone `Asia/Jakarta`, database size `26343103` bytes. Handshake was performed in a transaction with `transaction_read_only=on`. DB identity fingerprint: `bc1f8e600fa3f7bc7cd14eca799025043527983b4be6d098c13708ef755912b2`.

## 5. Pilot Database Identity Proof

`POSTGRES_RUNTIME_IDENTITY=VERIFIED`; `PILOT_OPERATIONAL_DB_IDENTITY=PROVEN`. Laravel migrations, Academic tables (`class_sessions`, `session_student_participants`, `student_attendance`, `session_teacher_participations`, `class_session_groups`, `schedule_changes`, `attendance_period_locks`) and non-scaffold pilot data are present.

## 6. Migration Alignment

Repository migration files: `41`. Database applied migrations: `39`. Alignment: `PARTIAL`, but understood. The two unapplied repository migrations are `2026_09_11_000020_add_academic_controlled_vocabulary_checks.php` and `2026_09_17_000001_create_attendance_semantic_foundation_tables.php`. No migration was executed. The database lacks `attendance_source_certifications` and `class_lineage_mappings`.

## 7. Recovery Checkpoint

Recovery path: `recovery/soc-i0/SOC-I0_20260919-090706/`. Source snapshot created: `YES`. Logical backup and schema dump created: `YES`. Snapshot excludes `.env*`, credentials, vendor, node_modules, runtime logs/cache, and macOS metadata.

## 8. Database Backup Verification

Custom archive: `1,021,669` bytes, SHA-256 `3eae1ece49099cbba3db2f502fa16a4f3bc7fccadf7fdc830453a061ab969849`; `pg_restore --list` parsed successfully. Schema dump: `135,440` bytes, SHA-256 `e0dfd34827ad8fbaf2f3f1e6df32253d85ca1b5a6a65d59413ca98a3309bf358`. Restore test: `NO`.

## 9. Class Session Profile

| Metric | Count |
|---|---:|
| Total | 1,270 |
| PLANNED | 1,097 |
| CONFIRMED | 0 |
| COMPLETED | 82 |
| CANCELLED | 91 |
| RESCHEDULED | 0 |
| Invalid/null status | 0 |

Planned start range: `2026-07-01 08:00:00+07` through `2026-12-31 10:00:00+07`. `COMPLETED` is not interpreted as `HELD`.

## 10. Student Attendance Profile

Total `student_attendance` rows: `1,414`. Distribution: `PRESENT=1,351`, `IZIN=40`, `SICK=17`, `ABSENT=6`. Legacy `EXCUSED=0`; unexpected status count `0`. No student names or IDs exported.

## 11. Session × Attendance Profile

`PLANNED_WITH_ATTENDANCE=0`; `CONFIRMED_WITH_ATTENDANCE=0`; `COMPLETED_WITH_ATTENDANCE=82`; `COMPLETED_WITHOUT_ATTENDANCE=0`; `CANCELLED_WITH_ATTENDANCE=0`; `RESCHEDULED_WITH_ATTENDANCE=0`.

## 12. Cancellation Profile

Cancelled sessions with effective attendance anomaly: `0`. Cancelled sessions without a linked non-empty cancellation reason: `26`. No correction was attempted.

## 13. Reschedule Profile

Rescheduled sources: `0`; replacements: `0`; broken lineage: `0`; rescheduled sources with attendance: `0`. No repair was attempted.

## 14. Joint Session Profile

Sessions with more than one `class_session_groups` row: `309`. Maximum groups per session: `2`. Joint occurrence grain conflict: `NOT_DERIVABLE` from current aggregate schema evidence. Multiple groups confirm that one institutional session can span multiple classes.

## 15. Participant Snapshot Profile

Participant rows: `26,261`; sessions with snapshots: `1,267`; sessions without snapshots: `3`; duplicate `(class_session_id, student_id)` rows: `0`.

## 16. Teacher Participation Profile

Total participation rows: `82`; PRIMARY `64`; SUBSTITUTE `18`; unexpected roles `0`. Teacher attendance statuses: `PRESENT=61`, `ABSENT=2`, `IZIN=16`, `NULL=3`. Teacher attendance rows with a present note: `0`; unexpected teacher attendance status `0`.

## 17. Substitution Profile

Sessions with substitute: `18`; multiple substitutes in one session: `0`; substitution ScheduleChange rows: `19`; substitute conflict: `NOT_DERIVABLE`. Existing substitution remains session-level; no RBAC or substitution change was made.

## 18. Teacher Attendance Profile

Teacher participation rows with non-null attendance: `79`. Current runtime vocabulary is compatible with `PRESENT`, `ABSENT`, `SICK`, `IZIN`, `OTHER`; unexpected count `0`. A `PRESENT + reason/note` lateness representation exists in the schema, but current aggregate count is `0`.

## 19. Schedule Change Profile

Types: `CANCELLATION=65`, `IMPORT_CORRECTION=52`, `SCHEDULE_REVISION=26`, `SUBSTITUTION=19`. Status: `APPLIED=162`. Missing all actor lineage fields: `52`; missing reason: `0`.

## 20. Period Lock / Publication Profile

`attendance_period_locks` exists, but current row counts are `ACADEMIC_PERIOD_TOTAL=0`, `LOCKED=0`, `OPEN=0`; locked periods containing sessions `0`. Published report-card/transcript outputs: `0`. Periods affected by occurrence rebase: `NOT_DERIVABLE`.

## 21. Historical Snapshot / Migration Profile

`monthly_attendance_summaries=5`; `monthly_student_attendance_snapshots=84`; `import_batches=2`; `import_lineages=89`. These remain legacy/import lineage evidence and were not used as live attendance authority.

## 22. Source Authority Runtime State

`SOURCE_AUTHORITY_RUNTIME_STATE=NOT_PRESENT` in this database: the repository contains the source-certification implementation, but the corresponding `attendance_source_certifications` table is absent because the latest semantic-foundation migration is unapplied. `class_lineage_mappings` is also absent. No source authority decision was resolved.

## 23. Data Quality Anomaly Summary

- CRITICAL: `0` confirmed from aggregate checks; no destructive action taken.
- HIGH: `3` sessions without participant snapshots; occurrence/attendance coverage must not silently assume a roster.
- HIGH: `26` cancelled sessions without a linked non-empty cancellation reason; owner: Academic data-quality/recovery review.
- MEDIUM: `381` past PLANNED sessions; owner: Academic operational review, not automatic occurrence mapping.
- MEDIUM: migration alignment partial and source-authority tables absent; owner: release/database gate.
- LOW: `52` ScheduleChange rows lack all actor lineage fields; owner: audit/data-quality review.

## 24. SOC-CAS Runtime Assumption Validation

No architecture-critical assumption was contradicted (`0`). Confirmed: multiple class groups per session, session-level teacher participation/substitution, current-status cancellation, student attendance linked through session participants, and teacher participation linked to sessions. Reschedule lineage is structurally supported but has no current rows. Period-lock mechanism exists but has no data. Legacy snapshot/import distinction is present. Dashboard dependence on transitional workflow population is confirmed by source inspection.

## 25. Cutover Boundary Analysis

Preferred future boundary: `TRANSACTION_CREATED_AFTER_AUTHORIZED_SOC_I1_SCHEMA_AND_SERVICE_CUTOVER`. It avoids automatic historical `COMPLETED→HELD` inference, preserves pilot history, and gives new occurrence records an explicit evidence boundary. Date/period/term boundaries remain secondary options requiring publication and lock evidence. This boundary was not enacted.

## 26. Pre-Implementation Risk Assessment

Primary risks are the two unapplied migrations, absent runtime source-certification/class-lineage tables, three sessions without participant snapshots, cancelled sessions lacking linked reasons, and a large historical PLANNED work queue. These are evidence/blockers only; no repair or migration was performed.

## 27. Remaining Blockers

Explicit implementation authorization is absent. PostgreSQL migration review and backup/restore rehearsal remain required. The unapplied semantic foundation migration must be reviewed before any source-authority or occurrence work. Historical occurrence classification, Waka scoped substitution policy delta, joint-session conflict evidence, and publication/lock impact require a future authorized phase.

## 28. Gate Decision

`SOC_I0_GATE_RESULT=PASS` for runtime identity, evidence capture, and recovery checkpoint, with implementation still blocked by governance/ migration/recovery gates. `SESSION_OCCURRENCE_IMPLEMENTATION_AUTHORIZED=NO`. No source or business-data mutation occurred.

## Machine-readable state

SOC_I0_COMPLETED = YES
SOC_I0_GATE_RESULT = PASS
POLICY_V1_1_HASH_VALID = YES
SOC_CAS_SPEC_HASH_VALID = YES
CIBS_BASELINE_HASH_VALID = YES
APP_DATABASE_DRIVER = pgsql
APP_DATABASE_HOST_CLASS = LOOPBACK
POSTGRES_RUNTIME_IDENTITY = VERIFIED
PILOT_OPERATIONAL_DB_IDENTITY = PROVEN
DB_IDENTITY_FINGERPRINT_SHA256 = bc1f8e600fa3f7bc7cd14eca799025043527983b4be6d098c13708ef755912b2
REAL_POSTGRES_RUNTIME = VERIFIED
REPOSITORY_MIGRATION_FILE_COUNT = 41
DATABASE_APPLIED_MIGRATION_COUNT = 39
MIGRATION_STATE_ALIGNMENT = PARTIAL
B2C_APPLICATION_SOURCE_CONTINUITY = PASS
APPLICATION_SOURCE_HASH_MISMATCH_COUNT = 0
APPLICATION_SOURCE_SNAPSHOT_CREATED = YES
DATABASE_BACKUP_CREATED = YES
DATABASE_BACKUP_ARCHIVE_PARSE_VALID = YES
DATABASE_BACKUP_RESTORE_TESTED = NO
DATABASE_BACKUP_SHA256 = 3eae1ece49099cbba3db2f502fa16a4f3bc7fccadf7fdc830453a061ab969849
DATABASE_SCHEMA_DUMP_CREATED = YES
DATABASE_SCHEMA_DUMP_SHA256 = e0dfd34827ad8fbaf2f3f1e6df32253d85ca1b5a6a65d59413ca98a3309bf358
RECOVERY_PATH = /Users/afradadmedia/DATA/PTAFRADAD/CHATGPTLOCAL/SISTEM-IMTAQ/recovery/soc-i0/SOC-I0_20260919-090706
RECOVERY_CHECKPOINT_COMPLETE = YES
RECOVERY_HASH_MANIFEST_VALID = YES
CLASS_SESSION_TOTAL = 1270
SESSION_PLANNED_COUNT = 1097
SESSION_CONFIRMED_COUNT = 0
SESSION_COMPLETED_COUNT = 82
SESSION_CANCELLED_COUNT = 91
SESSION_RESCHEDULED_COUNT = 0
SESSION_INVALID_STATUS_COUNT = 0
PAST_PLANNED_COUNT = 381
PAST_CONFIRMED_COUNT = 0
FUTURE_COMPLETED_COUNT = 0
STUDENT_ATTENDANCE_TOTAL = 1414
LEGACY_EXCUSED_COUNT = 0
UNEXPECTED_ATTENDANCE_STATUS_COUNT = 0
PLANNED_WITH_ATTENDANCE_COUNT = 0
CONFIRMED_WITH_ATTENDANCE_COUNT = 0
COMPLETED_WITH_ATTENDANCE_COUNT = 82
COMPLETED_WITHOUT_ATTENDANCE_COUNT = 0
CANCELLED_WITH_ATTENDANCE_COUNT = 0
RESCHEDULED_WITH_ATTENDANCE_COUNT = 0
CANCELLED_WITH_EFFECTIVE_ATTENDANCE_ANOMALY_COUNT = 0
RESCHEDULED_SOURCE_COUNT = 0
RESCHEDULED_REPLACEMENT_COUNT = 0
RESCHEDULE_LINEAGE_BROKEN_COUNT = 0
RESCHEDULED_SOURCE_WITH_ATTENDANCE_COUNT = 0
JOINT_SESSION_COUNT = 309
MAX_GROUPS_PER_SESSION = 2
JOINT_OCCURRENCE_GRAIN_CONFLICT_COUNT = NOT_DERIVABLE
SESSION_PARTICIPANT_TOTAL = 26261
SESSIONS_WITH_PARTICIPANT_SNAPSHOT = 1267
SESSIONS_WITHOUT_PARTICIPANT_SNAPSHOT = 3
DUPLICATE_SESSION_PARTICIPANT_COUNT = 0
TEACHER_PARTICIPATION_TOTAL = 82
PRIMARY_PARTICIPATION_COUNT = 64
SUBSTITUTE_PARTICIPATION_COUNT = 18
UNEXPECTED_TEACHER_ROLE_COUNT = 0
SESSIONS_WITH_SUBSTITUTE = 18
MULTI_SUBSTITUTE_SESSION_COUNT = 0
SUBSTITUTION_CHANGE_COUNT = 19
SUBSTITUTION_CONFLICT_COUNT = NOT_DERIVABLE
TEACHER_ATTENDANCE_TOTAL = 79
TEACHER_PRESENT_WITH_NOTE_COUNT = 0
UNEXPECTED_TEACHER_ATTENDANCE_STATUS_COUNT = 0
ACADEMIC_PERIOD_TOTAL = 0
ACADEMIC_PERIOD_LOCKED_COUNT = 0
ACADEMIC_PERIOD_OPEN_COUNT = 0
PUBLISHED_ACADEMIC_OUTPUT_COUNT = 0
PUBLISHED_PERIODS_AFFECTED_BY_OCCURRENCE_REBASE = NOT_DERIVABLE
SOURCE_AUTHORITY_RUNTIME_STATE = NOT_PRESENT
LEGACY_SESSION_ROWS_REQUIRING_OCCURRENCE_STRATEGY = 1270
HISTORICAL_COMPLETED_AUTO_HELD = NO
RECOMMENDED_CANONICAL_OCCURRENCE_CUTOVER_BOUNDARY = TRANSACTION_CREATED_AFTER_AUTHORIZED_SOC_I1_SCHEMA_AND_SERVICE_CUTOVER
SOC_CAS_RUNTIME_ASSUMPTIONS_CONTRADICTED = 0
APPLICATION_SOURCE_CHANGED = NO
APPLICATION_TEST_SOURCE_CHANGED = NO
PILOT_DATABASE_INSERT = 0
PILOT_DATABASE_UPDATE = 0
PILOT_DATABASE_DELETE = 0
PILOT_DATABASE_DDL = 0
MIGRATION_CREATED = NO
MIGRATION_EXECUTED = NO
SEEDER_EXECUTED = NO
STUDENT_NAMES_EXPORTED = NO
PARENT_DATA_EXPORTED = NO
INDIVIDUAL_STUDENT_RECORDS_EXPORTED = NO
DATABASE_PASSWORD_EXPOSED = NO
APPLICATION_SECRET_EXPOSED = NO
