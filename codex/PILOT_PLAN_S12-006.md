# Controlled Academic Pilot Plan — 1–2 Classes

- Task: `IMP-S12-006`
- Date prepared: 2026-09-04
- Pilot status: `PREPARATION`
- UAT status: `ACCEPTED` by Azhar, Wawan SN, and Abu Ubaidah

## Pilot scope

- Pilot class 1: 3A
- Pilot class 2 (optional): 3B
- Academic period: 4–12 September 2026
- Wali Kelas: Azhar
- Pilot coordinator: Wawan SN
- Planned start/end: 4 September 2026 / 12 September 2026

## Preconditions

- [x] Business UAT accepted.
- [x] Full application regression passed: 183 tests, 632 assertions.
- [x] Backup and restore verification passed.
- [x] Pilot class and period selected.
- [ ] Wali Kelas and Waka Akademik briefed on the workflow; briefing material prepared in `codex/PILOT_BRIEFING_S12-006.md`.
- [ ] Initial roster and schedule checked.
- [x] Backup checkpoint taken immediately before pilot data entry: `/private/tmp/imtaq_pilot_preentry_20260904.dump` (309 TOC entries; SHA-256 `ba88936ef851b207c86274c37d48a72b54650b1d8bd719907f4e7497bb6c8436`).

### Pre-pilot check — 2026-09-04

- Initial lookup before sample provisioning: classes, active homeroom assignments, and sessions were not found.
- Sample provisioning result: 2 pilot classes, 2 active homeroom assignments, 10 enrolled sample students, 2 approved schedule rules, and 4 planned sessions.
- Seeder verification: rerun is idempotent; no pending migration was applied.
- Result: structural prerequisites are available as sample data; briefing, backup checkpoint, and pilot data entry remain pending.

### Readiness verification — 2026-09-04

- [x] Active classes, homeroom assignments, roster, schedule rules, and planned sessions verified read-only.
- [x] Sample users have `WALI_KELAS` (Azhar) and `WAKA_AKADEMIK` (Wawan SN) role assignments.
- [ ] `user_staff_links` table is available; it is missing from the current database, so authenticated staff-scope access cannot yet be verified.
- [x] Session participant snapshots are prepared; 4 sessions now contain 20 expected participants.
- [x] Attendance and staff-link migrations applied in the approved narrow scope.
- [x] Azhar and Wawan SN accounts are linked to their canonical staff records.
- [ ] Backup checkpoint and workflow briefing completed.
- Result: `READY_FOR_BACKUP_AND_BRIEFING`; no attendance data entry performed.

### Controlled sample execution — 2026-09-04

- [x] Primary teacher participation created for all 4 sessions.
- [x] Teacher attendance recorded as PRESENT by Azhar for all 4 sessions.
- [x] Student attendance entered and finalized for all 4 sessions: per class 6 PRESENT, 2 ABSENT, and 2 IZIN with notes.
- [x] All 4 sessions moved to `COMPLETED`; attendance workflow status is `VALIDATED`.
- [ ] Waka Akademik review and pilot closeout confirmation.
- Result: `EXECUTION_COMPLETE_REVIEW_PENDING`.

### Waka Akademik technical review — 2026-09-04

- [x] Wawan SN dashboard access verified as `WAKA_AKADEMIK`.
- [x] Dashboard shows exactly 2 pilot classes: 3A and 3B.
- [x] Each class shows 2/2 completed sessions and 10/10 resolved attendance opportunities.
- [x] No technical defect found in the read-only review.
- [x] Business Owner and Waka Akademik closeout confirmation; closeout pack recorded in `codex/PILOT_CLOSEOUT_S12-006.md`.
- Result: `CLOSED — SAMPLE PILOT ACCEPTED`.

## Pilot execution checklist

1. Confirm class scope and effective Wali Kelas.
2. Confirm schedule/session exists for the selected period.
3. Snapshot expected student participants.
4. Record teacher attendance through the Wali Kelas workflow.
5. Record student attendance, including at least one note and one per-session IZIN if applicable.
6. Finalize attendance and verify Waka Akademik visibility.
7. Review dashboard, grade/report read paths, and data-quality indicators.
8. Record every issue in the defect log below; do not silently correct evidence.

## Success criteria

- Wali Kelas can complete the intended attendance workflow for the selected class.
- Waka Akademik can review the class result and applicable lock/correction path.
- No unauthorized cross-class visibility or mutation is observed.
- Attendance notes and per-session IZIN behavior are preserved.
- Dashboard/report values match the recorded canonical facts.
- No `BLOCKER` or `HIGH` defect remains open at pilot close.

## Pilot defect log

| ID | Date | Scenario | Expected | Actual | Severity | Owner | Status | Evidence |
|---|---|---|---|---|---|---|---|---|
| PILOT-001 |  |  |  |  |  |  | OPEN |  |

## Closeout

- Pilot result: `ACCEPTED — SAMPLE PILOT`
- Defects carried forward: ____________________
- Wali Kelas confirmation: ____________________
- Waka Akademik confirmation: Wawan SN — ACCEPTED — 2026-09-04
- Business Owner decision: Abu Ubaidah — ACCEPTED — 2026-09-04

This is a preparation document. It does not start the pilot or select classes on behalf of the business owner.
