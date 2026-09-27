# Conditional Legacy-Grain Persistence Design

Status: design checkpoint for `IMP-S11-003` — no migration created.

## Evidence currently available

- Synthetic daily/monthly fixtures now exist under `codex/samples/legacy/` for profiling and contract tests; they are not production evidence.
- Registered import files can declare `DAILY_LEVEL` or `MONTHLY_SUMMARY`; this is an inventory signal, not enough evidence to invent column semantics.
- The migration contract requires daily data to remain daily and monthly summaries to remain summaries. Known schedules must not create session facts.

## Conditional persistence plan

Create a new forward migration only after a real source sample is inventoried, profiled, and approved:

| Source grain | Candidate storage | Required preservation |
| --- | --- | --- |
| `DAILY_LEVEL` | `legacy_student_attendance_daily` | one record per source row; source date/status remain daily; no session expansion |
| `MONTHLY_SUMMARY` | `legacy_student_attendance_summaries` or a source-specific summary table | one record per source row; source period/aggregate remains monthly; no fabricated dates/sessions |

Every eventual legacy record must retain `import_batch_id`, `import_file_id`, and `import_row_id` lineage. Canonical student linkage remains nullable until identity review is approved. Unknown source fields stay in preserved raw payload rather than being guessed into canonical columns.

## Creation gates

1. Register the actual source file and checksum.
2. Profile representative rows and document field meanings, nullability, and grain.
3. Approve the field dictionary and target table name.
4. Add an additive migration with restrict-on-delete lineage foreign keys and targeted integrity tests.
5. Verify daily/monthly data cannot be queried as fabricated session attendance.

Until a real institutional source or an explicitly approved fixture profile satisfies gates 1–3, the safe action is to retain source rows in import infrastructure only.
