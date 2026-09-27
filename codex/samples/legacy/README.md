# Synthetic legacy source samples

These anonymized fixtures are for profiling and import-contract tests only. They are not production data and do not identify real students.

- `legacy_student_attendance_daily.sample.csv` has one record per source day.
- `legacy_student_attendance_monthly.sample.csv` has one aggregate record per student/month.
- Monthly totals must remain monthly summaries; they must not be expanded into session attendance.
- `legacy_student_key` is a source key only and requires identity review before any canonical linkage.
