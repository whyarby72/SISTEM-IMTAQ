# Change Manifest — K2B primary teacher participation

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`
**Type:** `CONTROLLED_PILOT_DATA_PROVISIONING`
**Repository:** `whyarby72/SISTEM-IMTAQ`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Preflight repository HEAD:** `38e14520c08daace2fc16b3608c73aa168fed471`
**Last executable CI before write:** `36829585309` = SUCCESS on `0ee6779ae2bbaaf44bf72d4996048c59f456848d`
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Authorized data change

PILOT identity was proven as database `imtaq`, PostgreSQL 18.6. The preflight used `BEGIN TRANSACTION READ ONLY` and proved `transaction_read_only=on`. It found exactly 12 reportable K2B anchor sessions, all with K2B and K3B `JOINT_SCOPE`; the K2B/K3B session sets were equal with intersection 12. All 12 had an authoritative `ClassSession -> TeachingAssignment -> teacher_staff_id` mapping. Existing physical participation and expected PRIMARY counts were 0/12, with no incompatible rows or conflicts.

The only write path used was `TeacherParticipationRecorder::ensurePrimary()` inside one outer transaction, once for each exact target session. The first guarded attempt rolled back after a stale in-memory relation was detected; the retry reloaded fresh relations and committed successfully.

## Result

- Physical net-new participation rows: **12**, exact target sessions only.
- `role=PRIMARY`: 12/12.
- `obligation_type=TEACHING_ASSIGNMENT`: 12/12.
- `participation_status=EXPECTED`: 12/12.
- `attendance_status IS NULL`: 12/12.
- Canonical teacher match: 12/12.
- K2B canonical coverage: **12/12**.
- K3B shared joint coverage: **12/12**, from the same 12 physical rows.
- K2B-only participation rows: 0; K3B-only participation rows: 0; exact set equality: true.

Independent postflight used a new read-only transaction and reconfirmed database identity, read-only mode, all semantic counts, shared-row equality, and non-target baselines: K1 10/10, K2A 12/12, K3A 0/14. Scope-group rows remained 24 and schedule rules 12.

## Side-effect and safety checks

Target student attendance facts: 0. Target teacher attendance facts: 0. Correction facts: 0. Relevant period lock blockers: 0. No schedule, session, scope-group, roster, account, role, or AI/provider mutation was performed. No separate K3B provisioning was performed.

## Repository scope

Only governance/evidence/routing artifacts are changed by the follow-up repository commit. Application source, tests, migrations, schema, runtime configuration, and deployment files remain unchanged. `SOC-MD-06` is preserved; `IMP-S12-007=NOT_STARTED`; Public Academic AI remains `OFF`.

**Result:** `ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K2B = COMPLETED / PASS / 12_OF_12_EXPECTED_PRIMARY`

**Shared result:** `K3B_SHARED_JOINT_PRIMARY_COVERAGE = 12_OF_12_FROM_SAME_12_PHYSICAL_ROWS`

**Next task:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B` (read-only).

