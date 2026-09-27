# Redacted Runtime Rollback Note

Before activation, the two occurrence-related environment keys were absent and the resolved runtime values were:

- `ACADEMIC_SESSION_OCCURRENCE_ENABLED`: `false`
- `ACADEMIC_SESSION_OCCURRENCE_CUTOVER_AT`: `null`

After activation:

- `ACADEMIC_SESSION_OCCURRENCE_ENABLED`: `true`
- `ACADEMIC_SESSION_OCCURRENCE_CUTOVER_AT`: `2026-09-20T00:00:00+07:00`

No unrelated `.env` values are recorded or exposed. Rollback requires an explicit management decision, restoring these two values, refreshing config cache, and re-running the read-only runtime verification.

