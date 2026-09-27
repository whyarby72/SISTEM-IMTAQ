# 07 — Business Rules and Invariants

## Identity
1. Student name is never a primary identifier.
2. `student_code` is permanent canonical IMTAQ identity.
3. NIS/NISN are mutable administrative identifiers.
4. Unknown NIS/NISN = NULL/no record, never placeholder.
5. Identifier correction never creates a new student.
6. Student exit never deletes student history.


## Class Master / Enrollment
1. Grade level and class section are separate data dimensions.
2. Section codes (A/B/C/...) are configurable data and must not be hard-coded application enums.
3. `classes` are academic-year-specific records.
4. Student identity is independent from class placement; current class is derived from effective-dated enrollment.
5. A new section in a future year must be addable without source-code changes.
6. Mid-year class transfer closes/opens effective enrollment records; no historical overwrite.
7. Reporting by grade level uses `grade_level_id`, never parsing `display_name`.

## Scheduling
1. Scheduling is class-specific.
2. Session times and daily session counts are variable.
3. Schedule Rule ≠ Class Session.
4. Academic calendar is checked before session generation.
5. Permanent schedule changes close old rule/create new effective version; no historical overwrite.
6. Teacher overlap is a HARD BLOCK: one `Staff_ID` cannot be an effective delivery teacher for overlapping Academic occurrences.
7. Class overlap is a HARD BLOCK: one `class_id` cannot have overlapping active Academic sessions.
8. Location conflict is policy/configuration-controlled; Codex must not invent exclusivity rules.
9. Conflict interval semantics are half-open `[start,end)`; exact endpoint adjacency is allowed unless a future buffer policy is approved.
10. Conflict evaluation uses effective dates plus the same canonical recurrence resolver used by session generation; weekday/time labels alone are insufficient.
11. Preflight conflict checks are advisory; every authoritative create/update/apply command must recheck inside the persistence transaction.
12. Substitution, swap, reschedule/time change and extra/ad-hoc sessions must validate the resulting teacher/class occupancy before atomic apply.
13. Concurrent scheduling writes for the same resources must be serialized/rechecked so stale reads cannot create double booking.
14. No normal `ignore conflict` bypass is permitted for teacher/class hard conflicts.
15. Session generation is idempotent.
16. DQ periodically detects effective schedule conflicts that bypass prevention through import/integration/anomaly.

Authority: `SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`.

## Student attendance
1. Digital attendance is canonical primary transaction.
2. Normal online inputter = Wali Kelas.
3. Paper = verification/backup only.
4. Missing attendance ≠ ABSENT.
5. CANCELLED session creates no absence opportunity.
6. RESCHEDULED source creates no attendance opportunity.
7. Attendance grain = expected Student × Class Session.
8. Routine Finalize performs system validation and yields VALIDATED without admin revalidation.
9. Open-period correction requires reason + audit, not second approval.
10. Post-lock edits require controlled correction; no whole-period unlock.
11. Permission event never auto-creates attendance.
12. If `permission_event_id` exists, student/reference consistency is mandatory.
13. New Wali Kelas does not implicitly gain historical edit rights of former Wali assignment.

## Teacher participation
1. SUBSTITUTE is a role, not an attendance status.
2. Approved swap is not teacher absence.
3. Substitution does not erase original teacher obligation.
4. NULL teacher attendance ≠ ABSENT; missing teacher attendance is DQ.
5. Official pesantren cancellation is not teacher absence and is excluded from teacher attendance evaluation.
6. When a teacher is absent but the session proceeds with a substitute or Wali Kelas, the original teacher is recorded with ABSENT/SICK/IZIN/OTHER and the delivering teacher has a separate participation record.
7. Student attendance remains recorded when a substitute or Wali Kelas delivers the session.

## Grades
1. MVP grade grain = Student × Subject × Semester.
2. No Tugas/Quiz/UTS/UAS in MVP.
3. Missing grade ≠ zero.
4. Explicit zero is valid only if official factual score is zero.
5. Score range 0–100.
6. One current grade record per Student×Subject×Semester.
7. Teaching assignment provenance may be NULL; do not fabricate provenance for transferred students.
8. Grade correction preserves old/new values, version and audit.
9. Report and transcript do not become grade sources.
10. No KKM/mastery/remedial/ranking/GPA without approved policy and matching transaction model.

## Reports/transcripts
1. Report card = publication artifact, not grade source.
2. Transcript = versioned publication artifact from semester grades, not from report PDF.
3. Published artifact versions are immutable.
4. Correct source, then regenerate/reissue.
5. Published identity/grade/signatory values are snapshots.
6. Internal notes are not automatically parent-facing.
7. Parent only receives PUBLISHED own-child artifacts when portal is implemented.

## KPI
1. One Metric, One Definition.
2. Official KPI uses official eligible transactions.
3. Missing transactions appear as Data Quality, not negative facts.
4. Attendance denominator = eligible Student × Session opportunities.
5. Aggregates use transaction denominator; avoid average-of-averages with unequal denominators.
6. Physical Presence = PRESENT + LATE over resolved validated attendance opportunities. `MISSING` attendance is not counted as `ABSENT`.
7. Generic Attendance % is not official until policy-approved.
8. No composite Academic Score.
9. Metric threshold ≠ metric formula.

## Alerts
1. Alert derives from fact; never replaces fact.
2. DQ, operational exception and student early warning are separate.
3. Alert is not automatic punishment/diagnosis.
4. Every alert has owner, severity, status, due/resolution path.
5. Rules are transparent and versioned.
6. Deduplicate active identical conditions.
7. Closed alerts are retained.
8. No composite student risk score.
9. Student warning thresholds remain inactive until approved.

## Migration
1. Preserve original legacy grain.
2. Never manufacture historical precision.
3. Schedule context alone does not prove a historical session occurred.
4. Missing legacy attendance ≠ ABSENT.
5. Missing legacy grade ≠ zero.
6. Names do not auto-match identity.
7. Original source files retained/checksummed.
8. Every imported canonical record traceable to source batch/file/row.
9. Dry run + reconciliation before acceptance.
10. Import must be idempotent.
## Shared Communication — Future Business Rules
1. Communication never becomes the Source of Truth for holidays, schedule changes, attendance, grades, reports or other domain facts.
2. Institutional communication distinguishes `ANNOUNCEMENT`, `REMINDER`, and `ACTION_REQUEST`.
3. A holiday/cancellation/postponement announcement may only be generated from an approved canonical event/change or explicitly authorized manual communication intent according to future policy.
4. Dynamic recipient audiences resolve from effective Staff/Organization/Role/Assignment data; provider-side groups are not the institutional audience authority.
5. Manual communication groups require owner, effective membership and audit.
6. Sender permission is scoped; authority to message Academic recipients does not imply Tahfizh/Kesantrian/institution-wide send rights.
7. Executive `KEPALA_UNIT`, `IDAROH`, and `YAYASAN` remain read-only by default and cannot broadcast merely because they can preview reports.
8. Scheduled reminders must be idempotent and derived from an official source event/schedule.
9. Teacher availability confirmation is not official teacher attendance.
10. `NO_RESPONSE` to a teacher confirmation request never becomes ABSENT.
11. `CONFIRMED` availability never becomes PRESENT attendance automatically.
12. `UNAVAILABLE` creates an operational exception routed to authorized Academic staff; substitution/swap/reschedule/cancellation remains a separate human-governed Academic workflow.
13. Provider failure never changes the source business event.
14. Communication response facts retain request/recipient/response/channel/timestamp lineage.
15. AI may draft communication text only under AI RBAC; AI does not authorize schedule changes or sensitive broadcasts.
