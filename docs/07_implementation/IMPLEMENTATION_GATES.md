# Implementation Gates

## Gate A — Repository & safe-change foundation
Requires Sprint 0 complete: runnable Laravel test harness, PostgreSQL dev setup, environment docs/project checks, Git/repository conventions, secret separation, change-impact/write-scope discipline, Change Manifest template, repeatable verification workflow, and documented development→staging→production/rollback target. Business users must not be required to select individual source files for deployment.

## Gate B — Core data integrity
Requires Core identity, lifecycle, enrollment, homeroom, RBAC and audit P0 tests passing.

## Gate C — Academic transaction readiness
Requires calendar, teaching assignment, schedule, session generation and schedule-exception integrity tests passing.

## Gate D — Student attendance pilot readiness
Requires Wali Kelas scoped entry/finalization, correction, period lock, handover, cancellation/reschedule rules and P0 tests passing.

## Gate E — Reporting readiness
Requires official source rules, snapshot immutability and relevant policy decisions for the official workflow being published.

## Gate F — Historical migration acceptance
Requires source inventory, dry run, row accounting, identity review, reconciliation and owner approval.

## Gate G — Production rollout
Requires UAT acceptance, zero open S1/S2 integrity/security defects, tested restore, security review, pilot exit criteria, management-approved operational policies for activated official workflows, version-aware deployment from an approved Git revision/release, staging evidence for the release, and a tested/credible rollback or feature-disable path.
