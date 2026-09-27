# 12 — UAT and Verification Matrix

## Acceptance gate
- All P0 tests PASS.
- All in-scope P1 tests PASS.
- No open Critical/High integrity defect.
- Negative RBAC tests completed.
- Correction/audit proven.
- Reports/KPI reconciled to transactions.
- Migration batches used in production reconciled.
- Backup restore tested before production.
- POLICY_PENDING features remain disabled/unassumed.
- Business-owner sign-off retained.

## P0/P1 test catalogue
### Core identity/lifecycle
- CORE-001 unique permanent Student_ID.
- CORE-002 duplicate names allowed without identity merge.
- CORE-003 unknown NIS/NISN does not create placeholder.
- CORE-004 duplicate active NISN blocked.
- CORE-005 identifier correction preserves same student + audit.
- LIFE-001 effective-dated class transfer preserves history.
- LIFE-002 exit stops future eligibility, not historical facts.
- LIFE-003 historical attendance/grades remain after dismissal/graduation.

### Class Master / Rombel
- CLS-001 create grade level 1 with classes 1 A and 1 B → both valid.
- CLS-002 add section 1 C later → no code/schema change.
- CLS-003 duplicate 1 A in same academic year/unit/grade level → blocked.
- CLS-004 same label 1 A in next academic year → valid distinct `class_id`.
- CLS-005 mid-year 1 A → 1 B transfer preserves prior attendance/session history.
- CLS-006 grade-level reporting aggregates 1 A + 1 B by `grade_level_id`, not name parsing.
- CLS-007 removing/not creating section B in next year does not delete historical class B.

### Homeroom/RBAC
- HR-001 Wali X-A allowed own class attendance.
- HR-002 Wali X-A denied X-B attendance.
- HR-003 overlapping primary homeroom assignment blocked.
- HR-004 new Wali denied normal historical edit of prior Wali sessions.
- HR-005 controlled handover completion requires exception permission/reason/audit.

### Calendar/scheduling/session
- CAL-001 BLOCK calendar event prevents recurring session generation.
- CAL-002 REVIEW_REQUIRED produces exception path.
- SCH-001 variable time/daily session count supported.
- SCH-003 permanent changes preserve historical rule.
- SCH-CF-001 same teacher, different classes, 08:00–09:30 vs 09:00–10:30 → HARD BLOCK.
- SCH-CF-002 same teacher, 08:00–09:30 vs 09:30–11:00 → allowed with current zero-buffer policy.
- SCH-CF-003 same class, different teachers, overlapping interval → HARD BLOCK.
- SCH-CF-004 same teacher/time on different concrete dates → no conflict.
- SCH-CF-005 non-overlapping effective date ranges → no recurring-rule conflict.
- SCH-CF-006 recurrence rules sharing weekday/time but producing no common concrete occurrence → no conflict.
- SCH-CF-007 WEEK_OF_MONTH conflict uses canonical occurrence resolver, not name/string comparison.
- SCH-CF-008 preflight passes, competing write commits first, final save recheck → second conflicting write rejected.
- SCH-CF-009 two concurrent conflicting creates for same teacher/class → at most one commits.
- SCH-CF-010 cancelled/rescheduled source session does not retain an obsolete future slot.
- SCH-CF-011 imported/integration-created effective overlap is found by `ACA_DQ_SCHEDULE_CONFLICT`.
- SES-001 generator retry is idempotent.
- SES-002 roster snapshot preserves historical eligibility.
- SES-003 student exit removes only future eligibility.

### Student attendance
- ATT-001 Guru normal online create/finalize denied.
- ATT-002 Wali can save DRAFT.
- ATT-003 one missing required participant blocks finalize.
- ATT-004 missing record never becomes ABSENT.
- ATT-005 successful Finalize → attendance VALIDATED.
- ATT-006 no routine Admin revalidation step.
- ATT-007 Finalize may set eligible session COMPLETED.
- ATT-008 cancelled/rescheduled source cannot finalize normal attendance.
- ATT-009 duplicate attendance blocked.
- ATT-010 permission reference mismatch blocked when reference present.

### Paper
- PAPER-001 paper absent does not block valid online finalization.
- PAPER-002 outage paper workflow can later be entered digitally by Wali.
- PAPER-003 discrepancy does not auto-overwrite digital/source.

### Lock/correction
- LOCK-001 incomplete period cannot lock.
- LOCK-002 lock is scoped; does not lock unrelated class.
- LOCK-003 Wali direct edit after lock denied.
- COR-001 open correction requires reason + version/audit.
- COR-002 post-lock direct update denied.
- COR-003 approved targeted correction does not unlock entire month.
- COR-004 rejected correction leaves canonical data unchanged.

### Schedule exceptions
- SUB-001 substitution retains original obligation + adds substitute.
- SUB-CF-001 replacement/substitute teacher with overlapping delivery obligation → substitution rejected; source unchanged.
- SWAP-001 approved swap does not mark false absence.
- SWAP-CF-001 swap producing conflict for either teacher → whole swap rejected atomically; no partial side applied.
- RES-001 source rescheduled + replacement linked; no double attendance opportunity.
- RES-CF-001 replacement interval conflicts with teacher/class → reschedule rejected before source becomes RESCHEDULED.
- CAN-001 cancelled session creates no student absence.
- EXT-001 selected-student extra session does not mark others absent.
- EXT-CF-001 extra/ad-hoc session with teacher/class overlap → HARD BLOCK.

### Grades
- GRD-001 one Student×Subject×Semester grade.
- GRD-002 duplicate final grade blocked.
- GRD-003 missing grade ≠ zero.
- GRD-004 explicit zero accepted if factual.
- GRD-005 score outside 0–100 blocked.
- GRD-006 no Tugas/Quiz/UTS/UAS required in MVP.
- GRD-007 transfer with same subject still expects one final semester grade.
- GRD-008 ambiguous teaching-assignment provenance does not fabricate FK.
- Grade workflow/lock actor tests = BLOCKED_BY_POLICY until decided.

### Report/transcript
- RPT-001 missing expected grade blocks official report.
- RPT-002 attendance periods not locked block official publication.
- RPT-003 report UI cannot edit grade source.
- RPT-004 published snapshot remains unchanged after later source correction.
- RPT-005 reissue creates v2; v1 SUPERSEDED, not deleted.
- TRN-001 repeated subject across semesters remains separate history facts.
- TRN-002 transcript reads semester grades, not report PDF.
- TRN-003 published transcript snapshot immutable.

### KPI
Synthetic attendance dataset: 100 opportunities; PRESENT80/LATE10/SICK4/PERMISSION3/EXCUSED1/ABSENT2.
Expected Physical Presence=90%, Unexcused Absence=2%, completeness=100%.
- KPI-001 cancelled sessions do not change attendance denominator.
- KPI-002 rescheduled source not double-counted.
- KPI-003 missing attendance reduces completeness but does not increase absence.
- KPI-004 average-of-averages with unequal denominators is prohibited; aggregate from transaction grain.
- KPI-005 missing expected grade prevents official aggregate from being treated final.

### Alerts
- DQ-001 missing attendance creates alert.
- DQ-002 clearing deterministic condition resolves alert.
- DQ-003 repeated evaluation does not create duplicate active alerts.
- DQ-004 recurrence after closure creates new occurrence.
- DQ-005 inactive student-warning rule produces no warning even if raw values are high.

### Migration
- MIG-001 checksum/raw source retained.
- MIG-002 names without stable ID do not auto-match.
- MIG-003 daily attendance remains daily; known schedule does not fabricate sessions.
- MIG-004 monthly teacher attendance count does not fabricate session facts.
- MIG-005 semester final grade may import canonically with provenance.
- MIG-006 dry run creates no canonical records.
- MIG-007 rerun is idempotent.
- MIG-008 all source rows reconcile exactly.
- MIG-009 imported target traceable to row/file/batch.

### Concurrency/atomicity
- CON-001 optimistic concurrency rejects stale version save.
- CON-002 double-finalize yields one logical finalization.
- CON-003 schedule generator retry yields no duplicate.
- CON-004 schedule change apply retry is idempotent.
- CON-005 multi-step student status change is atomic.

## Production technical gate
Backup must be restored in a test environment and core records verified. A backup that has never been restored is not accepted as a recovery strategy.
