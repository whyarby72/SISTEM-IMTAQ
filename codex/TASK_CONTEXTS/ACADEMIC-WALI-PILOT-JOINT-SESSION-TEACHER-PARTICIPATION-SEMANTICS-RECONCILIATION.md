# Joint-session teacher participation semantics reconciliation

**Task ID:** `ACADEMIC-WALI-PILOT-JOINT-SESSION-TEACHER-PARTICIPATION-SEMANTICS-RECONCILIATION`
**State:** `COMPLETED / PASS / JOINT_SESSION_TEACHER_PARTICIPATION_SEMANTICS_RATIFIED`
**Mode:** read-only. **Database write:** NONE.

## Required evidence

Use audited repository HEAD `0e60c8053b40d160d0dc1d3c8388e532c1b81fc9`, accepted CI run `36826834884`, and a PILOT `BEGIN TRANSACTION READ ONLY` with `transaction_read_only=on`. Record aggregates only.

## Ratified contract

`SessionTeacherParticipation` is one teacher-participation fact per `ClassSession` and teacher. Class scope is derived from `ClassSessionGroup`; it is not duplicated in the participation table. `ensurePrimary()` is the only canonical provisioning service and is idempotent for the session/teacher key.

For a joint K2B+K3B session, one PRIMARY row is visible to both effective class scopes. A future K2B write of 12 rows therefore yields K2B `12/12` and K3B `12/12` coverage from the same 12 rows. K3B must not be provisioned separately.

## Routing

The K2B controlled provisioning task remains next. Its preflight must prove K3B baseline `0/12` before the write. Its postconditions and independent postflight must prove K2B `12/12`, K3B `12/12` via the same shared row count/IDs, no duplicate/conflict, and no non-target mutation. K1 remains `10/10`, K2A `12/12`, K3A `0/14`, and K3B session/scope data remains unchanged.

Preserve `SOC-MD-06`, `IMP-S12-007=NOT_STARTED`, and Public Academic AI `OFF`. No schema gap, source change, migration, or additional remediation is authorized by this checkpoint.

