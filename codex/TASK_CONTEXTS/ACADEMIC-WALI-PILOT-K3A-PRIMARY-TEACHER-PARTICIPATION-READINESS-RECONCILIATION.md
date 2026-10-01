# K3A primary teacher participation readiness

**Task ID:** `ACADEMIC-WALI-PILOT-K3A-PRIMARY-TEACHER-PARTICIPATION-READINESS-RECONCILIATION`
**State:** `COMPLETED / READ_ONLY / K3A_PRIMARY_PROVISIONING_READY_FOR_CONTROLLED_PREFLIGHT`
**Horizon:** `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

This checkpoint is read-only. It proved PILOT identity, PostgreSQL 18.6,
`BEGIN TRANSACTION READ ONLY`, `transaction_read_only=on`, and aggregate-only
evidence.

K3A has 14 anchor, 14 canonical, and 14 reportable sessions. All 14 are
`STANDALONE_K3A`; there are no joint sessions or shared K2B/K3B rows. All
teacher mappings are authoritative (14/14), existing expected PRIMARY is
0/14, missing physical rows are 14, and duplicate/conflict/lock/fact counts
are zero.

K1 remains 10/10, K2A 12/12, K2B 12/12, and K3B 12/12 from the same 12
shared K2B/K3B rows. No provisioning is authorized by this context.

The next task, if approved after ChatGPT/project-owner audit, must be a
separate controlled K3A provisioning contract with a fresh mandatory
read-only preflight, canonical `TeacherParticipationRecorder::ensurePrimary()`
only, maximum 14 K3A rows, one outer transaction, and independent read-only
postflight. Do not provision K3B or alter K2B/K3B.

