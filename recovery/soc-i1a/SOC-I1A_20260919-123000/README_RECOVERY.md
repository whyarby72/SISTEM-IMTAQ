# SOC-I1A-R1 Recovery Checkpoint

Date: 2026-09-19
Database: `imtaq`
State: 42 migrations applied, 0 pending

This checkpoint records the repaired canonical occurrence persistence migration after the first SOC-I1A pilot attempt failed safely with PostgreSQL SQLSTATE 42830. The failed attempt left no schema remnants. The repair is limited to the original SOC-I1A persistence architecture: append-only occurrence versions, nullable effective pointer, actor/partial metadata, and database-enforced same-session lineage.

Included source artifacts:

- `2026_09_19_000001_create_session_occurrence_versions_table.php`
- `SessionOccurrenceVersion.php`
- `ClassSession.php`
- `SessionOccurrencePersistenceTest.php`

Included database artifacts:

- `POST_SOC_I1A_DATABASE.dump` — custom-format post-migration backup
- `POST_SOC_I1A_SCHEMA.sql` — schema-only backup

The backup is validated with `pg_restore --list`. No occurrence rows or effective pointers were created. No historical attendance, session status, denominator, source-authority, or eligibility data was rewritten. Occurrence write service and denominator cutover remain unstarted.
