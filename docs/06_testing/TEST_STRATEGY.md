# Test Strategy

The detailed UAT catalogue is in `UAT_MATRIX.md`. This file defines implementation practice.

## Layers
- Database constraint tests.
- Domain/service unit tests.
- Workflow/integration tests.
- Authorization positive and negative tests.
- KPI reconciliation tests.
- Migration idempotency/reconciliation tests.
- Concurrency and atomicity tests.
- Business UAT.

## Release rule
- All in-scope P0 tests must pass.
- All in-scope P1 tests must pass before production rollout.
- Open S1/S2 integrity/security defects block rollout.
- `POLICY_PENDING` tests remain blocked/disabled, never auto-passed.

## Required regression practice
When a production/pilot bug violates a business rule, add a failing regression test before or with the fix so the defect cannot silently return.
