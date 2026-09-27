# PostgreSQL Constraint Evidence

Verified on disposable rehearsal and pilot `imtaq` after retry:

- `UNIQUE (class_session_id, version_no)` exists.
- `UNIQUE (class_session_id, id)` exists as the composite candidate key.
- `FOREIGN KEY (class_session_id, supersedes_version_id) REFERENCES session_occurrence_versions(class_session_id, id) ON DELETE RESTRICT` exists.
- `FOREIGN KEY (id, effective_occurrence_version_id) REFERENCES session_occurrence_versions(class_session_id, id) ON DELETE RESTRICT` exists.
- occurrence status CHECK permits only `SCHEDULED`, `HELD`, `CANCELLED`, `RESCHEDULED`.
- occurrence version `class_session_id` FK and replacement-session FK use `ON DELETE RESTRICT`.

Negative PostgreSQL tests on disposable data:

- cross-session supersedes: rejected;
- cross-session effective pointer: rejected;
- duplicate `(class_session_id, version_no)`: rejected;
- invalid `COMPLETED` occurrence status: rejected.

Valid same-session supersedes and effective pointer operations were accepted inside a transaction that was rolled back.
