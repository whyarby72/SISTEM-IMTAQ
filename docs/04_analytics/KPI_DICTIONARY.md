# 09 — Academic KPI Dictionary v1.0

All metrics must have one definition, source, eligibility and version.

## Attendance metrics
### `ACA_ATT_OPPORTUNITY_COUNT`
Count of `EXPECTED + required` student participants on effective `COMPLETED` sessions. Exclude CANCELLED, RESCHEDULED source, REMOVED/non-required participants and post-exit ineligible participants.

### `ACA_ATT_PRESENT_COUNT`
Validated PRESENT attendance among eligible opportunities.

### `ACA_ATT_LATE_COUNT`
Validated LATE attendance.

### `ACA_ATT_SICK_COUNT`
Validated SICK attendance.

### `ACA_ATT_PERMISSION_COUNT`
Validated PERMISSION attendance.

### `ACA_ATT_EXCUSED_COUNT`
Validated EXCUSED attendance.

### `ACA_ATT_ABSENT_COUNT`
Validated explicit ABSENT attendance only.

### `ACA_ATT_PHYSICAL_PRESENCE_PCT`
`(PRESENT + LATE) / RESOLVED * 100`.

`RESOLVED` means eligible attendance opportunities with a `VALIDATED` controlled attendance status. This metric describes physical presence among resolved records; it is not a completeness metric.
Official use requires completeness 100% and relevant period LOCKED.

### `ACA_ATT_UNEXCUSED_ABSENCE_PCT`
`ABSENT / RESOLVED * 100`.

`MISSING` records are not counted as `ABSENT`. This metric counts only explicit validated `ABSENT` statuses among resolved records.

### `ACA_ATT_COMPLETENESS_PCT`
`RESOLVED / ELIGIBLE * 100`.
This is a Data Quality/operational metric, not a student-performance metric.

### Attendance eligibility and null semantics

The canonical attendance opportunity is a student participant where:

- `participant_status = EXPECTED`; and
- `is_required = true`.

`is_required = true` identifies a mandatory attendance participant and places the opportunity in the standard mandatory attendance KPI denominator. `is_required = false` identifies an optional/non-mandatory participant and is excluded from that denominator.

For the canonical attendance metrics:

- `ELIGIBLE` = mandatory attendance opportunities;
- `RESOLVED` = eligible opportunities with a `VALIDATED` controlled attendance status;
- `MISSING` = `ELIGIBLE - RESOLVED`;
- `MISSING` is not the same as `ABSENT`.

When `ELIGIBLE = 0`, physical presence, unexcused absence and completeness are `null`. When `ELIGIBLE > 0` and `RESOLVED = 0`, physical presence and unexcused absence are `null`, while completeness is `0%`.

## Academic Dashboard CSV export contract

The Academic Dashboard CSV is a stable, machine-readable pilot contract. It is read-only and uses the same dashboard semantic services and definitions described above. Current pilot code does not introduce an export version, schema version, header alias, or v2 schema; current headers must therefore not be renamed during the pilot.

### Row grain

- Without a selected semester: one row per class in the authorized user scope.
- With a selected semester: one row per class × subject/grade. Class attendance and session metrics may repeat on each subject row.

### Ordered headers

1. `role`
2. `class`
3. `attendance_completeness_pct`
4. `physical_presence_pct`
5. `session_completion_pct`
6. `extra_sessions`
7. `semester`
8. `subject`
9. `grade_expected`
10. `grade_locked`
11. `grade_mean`
12. `grade_official`

### Attendance export fields

- `attendance_completeness_pct` = `RESOLVED / ELIGIBLE × 100`.
- `physical_presence_pct` = `(PRESENT + LATE) / RESOLVED × 100`.
- Percentage values are numeric percentage points from `0` to `100`, not fractions from `0` to `1`, and do not include a literal `%` suffix.
- A null metric is emitted as a blank CSV field.
- Numeric zero remains numeric `0`; blank is distinct from zero.

### Compatibility and known limitations

The following decisions apply to the current pilot:

- CSV header rename: deferred.
- Versioned export/schema: deferred.
- Human-readable management export: future reporting workstream.

Known limitations are accepted pilot limitations: the filename is currently `academic-dashboard.csv`; the period is not encoded in the filename or CSV columns; denominator semantics depend on this data contract; and semester-selected exports repeat class attendance/session metrics per subject row.

## Session metrics
### `ACA_SESSION_COMPLETED_COUNT`
Effective completed sessions.

### `ACA_SESSION_CANCELLED_COUNT`
Effective cancelled sessions.

### `ACA_SESSION_EXTRA_COUNT`
Completed/planned extra sessions according to selected filter.

### `ACA_SESSION_COMPLETION_PCT`
`completed effective sessions / (completed + cancelled effective sessions) * 100`.
Do not double-count rescheduled source sessions.

## Grade metrics
### `ACA_GRADE_COMPLETENESS_PCT`
`official/finalized semester grades / expected Student×Subject grade facts * 100`.
Expected subjects derive from eligible enrollments + teaching assignments, collapsed to distinct subject per student/semester.

### `ACA_STUDENT_SEMESTER_MEAN`
Simple arithmetic mean of all official subject grades for the student in the semester. Official only when expected grades are complete. No subject weighting unless later approved.

### `ACA_SUBJECT_MEAN_SCORE`
Sum official subject scores / count eligible official subject grades for selected scope.

### `ACA_CLASS_MEAN_SCORE`
Sum all eligible official grade facts / count all eligible grade facts for class/scope. Do not average student averages when denominators differ.

### `ACA_SUBJECT_SCORE_CHANGE`
Current official score minus previous official score for the same student+subject across comparable semesters.

## Operational metrics
### `ACA_UNFINALIZED_ATTENDANCE_SESSION_COUNT`
Completed/eligible sessions whose student attendance is not finalized/validated.

### `ACA_REPORT_CARD_READINESS_PCT`
Eligible students satisfying all configured report readiness requirements / eligible students requiring report.

### `ACA_REPORT_PUBLISHED_COUNT`
Count published academic report cards in scope.

### `ACA_CRITICAL_DQ_OPEN_COUNT`
Count active CRITICAL DQ/integrity alerts in scope.

### `ACA_POST_LOCK_CORRECTION_COUNT`
Count approved/applied post-lock corrections in scope.

## Metrics explicitly not official in MVP
- Generic `ATTENDANCE_PCT` — POLICY_PENDING.
- SUBJECT_MASTERY — no approved threshold.
- REMEDIAL_COUNT — no remedial transaction engine.
- ASSIGNMENT_COMPLETION — no detailed assessment transactions.
- GPA/IP/IPK.
- Student/Class ranking.
- Teacher attendance percentage until workflow is approved.
- Composite Academic Score.

## Semantic layer requirements
Recommended views/services:
- `v_academic_session_fact`
- `v_student_attendance_fact`
- `v_semester_grade_fact`
- `v_attendance_completeness`
- `v_grade_completeness`
- `v_report_card_readiness`
- `v_student_academic_history`

Dashboard, export and API must use the same metric service/definition.
