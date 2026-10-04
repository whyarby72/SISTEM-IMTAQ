# Change Manifest — ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-C2

Date: 2026-10-05
Project: SISTEM-IMTAQ
Task: `ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-C2-HTTP-PARTITION-FINALIZATION-RECONCILIATION`
Branch: `feat/super-admin-user-access-preferences`

## Decision

`R2B_C2_IMPLEMENTED_PENDING_CI`

This change wires the already-tested C1 partition finalizer into the Wali
HTTP surface without changing the canonical finalizer contract. A joint Wali
finalizes only the authorized effective-class partition; the physical session
remains non-completed until every required partition is validated.

## Baseline and scope

- Starting HEAD: `4df596d45e6ba5699b325bea0ae65681ed499ba4`
- C1 tested executable: `7ba316397dde0c9de27ab1fd9e23fa1c28124c71`
- C1 exact CI: `37212105492` = SUCCESS
- Application scope: HTTP controller, attendance Blade surface, dashboard
  read model, draft optimistic-version precondition, and focused regression.
- No migration, schema, dependency, runtime-config, deployment, or main-branch
  change.
- PILOT database access/write: `NONE / NONE`
- Public Academic AI: `OFF`
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`
- `SOC-MD-06`: unchanged

## Implemented behavior

1. Removed the temporary joint-session HTTP finalization block.
2. Reused `SessionAttendanceScopeResolver` and reject participant/version IDs
   outside the authorized Wali partition before attendance writes.
3. Added server-owned partition read-only/finalization state and Indonesian
   post-finalization feedback.
4. Submitted `attendance_versions[participant_id]` values are checked before
   draft updates. The finalizer receives the post-draft versions and retains
   its C1 guards; ordinary draft-before-finalize behavior remains unchanged.
5. Dashboard Wali FINALIZED state is partition-complete; Waka/global state
   still requires globally completed sessions.
6. Completed-only export links remain unavailable while another joint
   partition is still pending.

## Tests and checks

Focused regression added in
`StudentAttendancePartitionedFinalizerTest` for:

- Wali HTTP partition finalization and feedback;
- cross-partition participant rejection before write;
- stale attendance-version rejection without partial finalization;
- Wali dashboard partition-finalized label.

Local checks passed:

- `composer validate --strict`
- `php artisan view:cache`
- `python3 scripts/check_project_structure.py`
- PHP lint for changed PHP files
- Pint `--test` for changed PHP files
- `git diff --check`

Local focused PHPUnit was not executed against the protected pilot identity;
the database guard rejected the local target before any test query/write.
Disposable PostgreSQL CI is authoritative for the test result.

## Verification pending

- Exact implementation commit: pending push
- Exact GitHub Actions run: pending
- Foundation/Academic regression counts: pending exact CI evidence

## Rollback

Revert the implementation commit(s) on this branch. No migration or persistent
schema rollback is required. Existing C1 finalizer behavior remains the
rollback baseline.

## Next atomic task

Return to ChatGPT for C2 audit after exact-head GitHub Actions verification.
