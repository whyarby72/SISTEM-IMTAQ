# Example — Business Change Request

Request:

> Wali Kelas should see a dashboard warning if today's attendance has not been finalized.

Do not specify files. Use `handoff/01_PROMPTS/06_FEATURE_OR_CHANGE_REQUEST.txt`.

Codex first identifies:
- Academic Attendance owner;
- Source of Truth;
- affected role/scope;
- whether this is a derived operational warning or new transaction;
- DQ/reporting impact;
- tests;
- whether a current/future task should own the implementation.

This avoids creating a duplicate attendance truth just for the dashboard.
