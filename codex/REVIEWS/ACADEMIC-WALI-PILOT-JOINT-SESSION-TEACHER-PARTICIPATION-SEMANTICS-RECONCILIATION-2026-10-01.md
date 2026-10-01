# Joint-session teacher participation semantics reconciliation

**Task:** `ACADEMIC-WALI-PILOT-JOINT-SESSION-TEACHER-PARTICIPATION-SEMANTICS-RECONCILIATION`

**Mode:** read-only reconciliation. **Database write:** NONE.

## Authority and evidence

- Repository: `whyarby72/SISTEM-IMTAQ`
- Branch: `chore/academic-wali-pilot-primary-k2a`
- Audited starting HEAD: `0e60c8053b40d160d0dc1d3c8388e532c1b81fc9`
- Latest accepted CI evidence: run `36826834884`, SUCCESS, commit `43938305d5c556627aa2e13d2b5887fe1502bac0`
- Frozen horizon: `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`
- Database transaction: `BEGIN TRANSACTION READ ONLY`; `transaction_read_only=on`; aggregate/non-PII queries only.

## Canonical grain and storage

`SessionTeacherParticipation` is a participation fact at **one teacher per ClassSession**. The model contains `class_session_id` and `teacher_staff_id`, but no `class_id` or class-scope column. The immutable schema has unique `(class_session_id, teacher_staff_id)` and a partial unique index `(class_session_id)` for `role=PRIMARY AND participation_status=EXPECTED`, plus foreign keys and controlled-vocabulary checks.

Class attribution is structural: `ClassSession -> ClassSessionGroup -> class_id`, with the anchor `ClassSession.class_id` as fallback. `AcademicClassScopeResolver::forSession()` and the teacher dashboard query both include scope-group classes. A joint session is one event and one teacher-participation row, visible in each effective class scope.

`TeacherParticipationRecorder::ensurePrimary()` locks the session and teacher obligation, resolves the canonical teacher from `ClassSession -> TeachingAssignment -> teacher_staff_id`, and calls `firstOrCreate` keyed by session and teacher with `PRIMARY / TEACHING_ASSIGNMENT / EXPECTED`. It is idempotent for the canonical obligation. `attendance_status` remains null until a separate attendance action.

## Pilot reconciliation

The read-only query reproduced exactly 12 reportable K2B sessions in the frozen horizon. Every target session has two scope groups, `{IMTAQ-2026-2B, IMTAQ-2026-3B}`, with K2B as anchor. Current expected PRIMARY count is `0/12`; no participation row is present for this target set. The previous operational baseline remains K1 `10/10`, K2A `12/12`, K3A `0/14`, and K3B `0/12` on the same 12 canonical joint sessions.

Conceptual dry-run of `ensurePrimary()` for the 12 K2B sessions produces **12 net-new rows**, not 24: one canonical teacher obligation per ClassSession. Class-scoped coverage after that write is:

| View | Canonical sessions | Expected PRIMARY rows | Physical rows |
|---|---:|---:|---:|
| K2B | 12 | 12/12 | 12 shared rows |
| K3B | 12 | 12/12 | the same 12 shared rows |

The old target contract `K2B=12/12 AND K3B=0/12` is **not satisfiable** after canonical K2B provisioning. `K3B=0/12` is valid only as the pre-write baseline, not as a post-write invariant. No second K3B provisioning is allowed; doing so would contradict the unique expected-primary index and create duplicate semantic obligations.

## Decision

`JOINT_SESSION_TEACHER_PARTICIPATION_SEMANTICS_RATIFIED`

The current application/data model can represent the required joint-session teacher participation without a schema gap. The K2B contract is revised so its preflight still proves K3B `0/12`, while postconditions and independent postflight prove K2B and K3B both `12/12` through the same 12 shared participation rows, with no K3B copy or extra write.

## Boundaries and next task

No application source, test, migration, schema, runtime configuration, schedule, roster, account, role, AI/provider, or database data was changed. `SOC-MD-06`, `IMP-S12-007=NOT_STARTED`, and Public Academic AI `OFF` remain unchanged.

Next atomic task: `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`, beginning with its fresh mandatory read-only preflight.

