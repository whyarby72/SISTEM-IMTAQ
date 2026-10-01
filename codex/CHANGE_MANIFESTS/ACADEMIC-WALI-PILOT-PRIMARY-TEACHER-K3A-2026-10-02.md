# Change Manifest — K3A primary teacher participation

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K3A`
**Type:** `CONTROLLED_PILOT_DATA_PROVISIONING`
**Repository:** `whyarby72/SISTEM-IMTAQ`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Preflight HEAD:** `27ca62aab15e46fdffe44e8a919b7b0d51d8d4f2`
**Preflight CI:** `36925985111 = SUCCESS` on the exact preflight HEAD
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Authorized database change

The target was the authorized PILOT database `imtaq`, PostgreSQL 18.6. A
fresh `BEGIN TRANSACTION READ ONLY` preflight proved
`transaction_read_only=on`, the exact K3A target, authoritative teacher
mapping, zero existing participation, zero conflicts, zero locks, and zero
attendance/correction facts. No names, raw UUIDs, credentials, or secrets
were recorded.

The target was exactly 14 scheduled, reportable, standalone
`IMTAQ-2026-3A` sessions in the frozen horizon. All 14 had valid schedule and
teaching-assignment links, valid subjects and time bounds, no reschedule
lineage, and one authoritative `TeachingAssignment.teacher_staff_id`.

## Controlled write

One outer database transaction called only:

`App\Domains\Academic\Services\TeacherParticipationRecorder::ensurePrimary()`

exactly once for each exact target session. No raw SQL INSERT, direct model
creation, teacher mapping, session/schedule/roster change, attendance write,
correction, lock, account, role, or AI/provider mutation was performed.

Persisted delta: **14 net-new `SessionTeacherParticipation` rows**, K3A only.

## In-transaction and independent postflight

Before commit and again in a new read-only transaction:

- K3A reportable/standalone target: **14/14**;
- expected PRIMARY: **14/14**;
- canonical teacher match: **14/14**;
- `role=PRIMARY`, `obligation_type=TEACHING_ASSIGNMENT`, and
  `participation_status=EXPECTED`: **14/14**;
- `attendance_status`, `checkin_at`, and `checkout_at` remained NULL: **14/14**;
- duplicate/conflicting PRIMARY rows: **0**;
- K1: **10/10**; K2A: **12/12**; K2B: **12/12**;
- K3B: **12/12 from the same shared 12 physical K2B/K3B rows**;
- relevant locks, student attendance facts, teacher attendance facts, and
  correction facts: **0**.

The independent postflight also reproved PILOT identity and
`transaction_read_only=on`. The K2B/K3B participation sets remained exactly
equal; no separate K3B provisioning occurred.

## Repository scope

Only governance, evidence, routing, and test-matrix metadata are changed in
the repository follow-up. Application source, application tests, migrations,
schema, runtime configuration, deployment files, and AI/provider state are
unchanged. `SOC-MD-06`, `IMP-S12-007=NOT_STARTED`, and Public Academic AI
`OFF` are preserved.

**Result:** `ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K3A = COMPLETED / PASS / 14_OF_14_EXPECTED_PRIMARY`

**Next task:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K3A` (read-only; not executed here).
