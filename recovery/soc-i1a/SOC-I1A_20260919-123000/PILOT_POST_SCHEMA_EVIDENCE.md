# Pilot Post-Retry Evidence

Pilot identity: database `imtaq`, user `imtaq_app`.

- applied migrations: 42
- pending migrations: 0
- `session_occurrence_versions` rows: 0
- non-null `class_sessions.effective_occurrence_version_id`: 0
- `class_sessions`: 1270
- `session_student_participants`: 26261
- `student_attendance`: 1414
- `session_teacher_participations`: 82
- `schedule_changes`: 162
- `class_session_groups`: 1575
- `monthly_attendance_summaries`: 5
- `monthly_student_attendance_snapshots`: 84

Session status distribution remained `CANCELLED 91`, `COMPLETED 82`, `PLANNED 1097`. Existing A constraints remained valid and B semantic foundation tables/columns remained inactive.

Post-migration schema-only SHA-256: `bf7b2e93e7bbdacaee6763c5a6e09ba0a60a825883a9dbc8f854b920f1b7ee62`.
