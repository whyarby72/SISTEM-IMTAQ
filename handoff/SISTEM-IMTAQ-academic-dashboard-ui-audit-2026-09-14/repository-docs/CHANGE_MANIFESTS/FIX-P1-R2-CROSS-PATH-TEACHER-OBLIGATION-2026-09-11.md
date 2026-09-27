# Change Manifest

- Change ID: FIX-P1-R2-CROSS-PATH-TEACHER-OBLIGATION-2026-09-11
- Status: COMPLETED — source hardening and local regression complete
- Git metadata: unavailable in repository workspace

## Writer inventory

| Writer | Responsibility | Canonical lock |
|---|---|---|
| `SubstitutionService::applySubstitution` | Creates expected substitute participation | YES; final conflict after lock |
| `SwapService::applySwap` | Creates two substitute obligations | YES; both teacher keys sorted, final checks after lock |
| `RescheduleService::apply` | Retires source and creates lineage target session | YES; source session lock then teacher lock, final check |
| `ExtraSessionCreator::create` | Creates EXTRA/AD_HOC actual session | YES; teacher lock then final check |
| `ClassSessionGenerator::generate` | Materializes scheduled actual sessions | YES; rule lock then teacher lock then final check |
| `TeacherParticipationRecorder::ensurePrimary` | Materializes PRIMARY participation | YES; session lock then teacher lock |
| `ScheduleRuleRevisionService::revise` | Changes future rule/assignment and invokes generator | INDIRECT via `ClassSessionGenerator` |
| `TeacherScheduleImporter` / `TeacherSchedulePublicationService` | Future rule/assignment import or workflow state only | NOT_APPLICABLE until materialization |
| `ScheduleRuleConflictChecker` | Read-only rule validation | NOT_APPLICABLE |

## Canonical lock

`application/web/app/Domains/Academic/Services/TeacherObligationLockService.php`

- Namespace: `academic-teacher-obligation:<teacher_id>`.
- PostgreSQL key: `hashtextextended(namespace, 0)` passed to `pg_advisory_xact_lock(bigint)`.
- Width: 64-bit PostgreSQL advisory key.
- Stability: deterministic across requests/processes for the same namespace.
- Multiple teachers: unique IDs sorted with `SORT_STRING` before acquisition, preventing opposite lock order.
- SQLite: no-op by design; not a PostgreSQL contention proof.

Global order is relevant `ClassSession` row locks first, then sorted teacher advisory keys, then dependent rows. `SwapService` orders its two session locks by ID before taking teacher keys.

## Final checks and integrity

All mutating actual-session writers retain UX preflight where present and now re-query authoritative conflicts after the canonical lock. Conflict universe includes active PRIMARY teaching assignments and EXPECTED SUBSTITUTE participations. `CANCELLED` and `RESCHEDULED` sessions are excluded. Intervals are half-open `[start,end)`.

No conflicting session, participation, schedule change, or success audit is created on a rejected path. Existing swap/reschedule/substitution lineage semantics remain unchanged.

## Verification

- Targeted writers/lock tests: **27 tests / 92 assertions / 0 failures**.
- Academic regression: **213 tests / 817 assertions / 0 failures**.
- Pint: PASS.
- View cache: PASS from current Phase 7 validation; no views changed in R2.
- Real PostgreSQL cross-path contention: `DEFERRED_TO_STAGING`.

## Deferred

Cross-path contention involving database processes, advisory-lock behavior, deadlock observation, and any writer outside the inventoried Academic boundary require staging. P1-R3 pre-staging gate is not started.
