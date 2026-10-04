# Task Context — Academic Web Grade Workflow G2

Task: `ACADEMIC-WEB-GRADE-WORKFLOW-G2-DRAFT-ENTRY-STATE-HARDENING`
Type: `FULL_STACK_TRANSACTIONAL_DRAFT_GRADE_ENTRY`
State: `COMPLETED / PASS / GRADE_G2_IMPLEMENTED_PASS`
Branch: `feat/super-admin-user-access-preferences`
Starting HEAD: `3083c51109922cf1f4814505177670ff8720e9f2`
Tested executable HEAD: `b3878d9f02fbbde06dc9e23b2562e8ad8eaacd88`
Exact CI: `37200924044` = `SUCCESS`
Manifest: `codex/CHANGE_MANIFESTS/ACADEMIC-WEB-GRADE-WORKFLOW-G2-2026-10-04.md`

G2 implements DRAFT-only semester-grade entry for the exact assigned subject
teacher. The write path is one atomic class batch with server-owned teaching
assignment and Staff provenance, mandatory optimistic version checks for
existing rows, explicit missing-versus-zero handling, no-op suppression, and
per-changed-row canonical audit. CHECKED and LOCKED rows fail closed in the
domain service and remain read-only in the UI.

Wali Kelas, Waka Akademik, and Super Admin roles alone do not receive DRAFT
entry authority. A Wali or Waka may write only when the same authenticated
identity independently resolves to the one exact active TeachingAssignment.

No migration, PILOT database write, PILOT feature seed, CHECK/LOCK/correction
route, report/transcript change, AI/provider change, or Public Academic AI
activation occurred. Academic Web remains `4/10 = 40% COMPLETE_EVIDENCED`.

## Next atomic task

`RETURN_TO_CHATGPT_FOR_ACADEMIC_WEB_GRADE_G2_AUDIT`

Do not start G3 automatically. Preserve `SOC-MD-06`, keep `IMP-S12-007` as
`NOT_STARTED`, and keep Public Academic AI OFF.
