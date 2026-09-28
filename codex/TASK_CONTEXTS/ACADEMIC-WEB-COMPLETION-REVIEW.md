# ACADEMIC WEB COMPLETION REVIEW

**Task ID:** `ACADEMIC_WEB_COMPLETION_REVIEW`  
**Task type:** `READ_ONLY_PRODUCT_COMPLETION_REVIEW`  
**Owner:** CODEX  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `chore/academic-web-completion-review-routing`  
**State-basis:** `dfaf585c299aa83fccef7be95d746f943242c192`

## Purpose

Establish one evidence-based answer to: **what is actually complete, partial,
blocked, deferred, or still missing in the Academic Web product?**

Do not treat historical task labels or the conversational "~97%" estimate as
proof. Recalculate completion only from repository evidence and an explicit
review denominator.

## Academic product scope

Review the implemented Academic Web system as one coherent operational product:

1. Student attendance.
2. Teacher attendance.
3. Teaching schedule and generated class sessions.
4. Substitution, swap, reschedule, cancellation, extra/ad-hoc sessions.
5. Semester subject grades and grade finalization.
6. Report-card readiness, draft/snapshot, note, approve/publish.
7. Academic history and transcript draft/approve/publish.
8. Role-specific operational/management views and exports.
9. Shared Academic controls required for trustworthy operation:
   RBAC, audit, correction/versioning, period lock, DQ/completeness, alerts that
   are already approved/implemented.
10. Web usability for current Academic roles:
    Wali Kelas, Waka Akademik, Super Admin where applicable.

## Explicit exclusions

Do not count these as missing Academic Web product scope unless an existing
approved Academic requirement explicitly depends on them:

- Parent Portal;
- WhatsApp/shared communications;
- biometrics;
- cross-domain Tahfizh/Kesantrian features;
- future assessment engine beyond the implemented semester-grade contract;
- future AI expansion;
- production deployment/cutover.

Public Academic AI remains OFF.

## Production-readiness separation

This task is NOT `IMP-S12-007 Production cutover readiness review`.

Do not make claims that the system is production-ready merely because
foundation CI is green or Academic implementation is functionally complete.

Deployment, hosting, backup/recovery environment evidence, secrets, monitoring,
rollback, release authority, and production migration review remain a separate
gate.

## Source-of-truth hierarchy

Use repository evidence in this order:

1. current source/tests/routes/views;
2. canonical governance/decision artifacts;
3. current `PROJECT_STATE.json`, `NEXT_ACTION.md`, `TASK_QUEUE.md`;
4. change manifests/recovery evidence;
5. exact Actions evidence.

If historical documentation conflicts with current source/evidence, record the
conflict rather than silently reconciling it.

## Review method

Build an explicit requirement inventory before assigning completion status.

For each reviewed capability, record:

- process/domain;
- process owner/user;
- source of truth;
- transaction grain;
- inputter;
- validator/approver;
- lifecycle/status;
- RBAC;
- audit/versioning;
- relevant data-quality rule;
- UI/route/API evidence;
- automated test evidence;
- report/decision consumer;
- integration dependency;
- completion status;
- gap/risk;
- exact evidence paths.

Allowed status vocabulary only:

- `COMPLETE_EVIDENCED`
- `PARTIAL`
- `GOVERNANCE_BLOCKED`
- `TECHNICAL_BLOCKED`
- `DEFERRED_OUT_OF_SCOPE`
- `NOT_EVIDENCED`

Do not use subjective scores.

## Completion percentage rule

A percentage may be reported only after defining a denominator of in-scope
review items.

Use:

`completion % = COMPLETE_EVIDENCED in-scope items / total in-scope items × 100`

Do not count `DEFERRED_OUT_OF_SCOPE` items in the denominator.

Show both:
- item-count completion; and
- critical-capability completion, where a critical capability is complete only
  if its transaction + validation + authorization + audit + user-facing path
  are all evidenced.

Do not round an incomplete critical workflow up to 100%.

## Mandatory review areas

### A. Transactional integrity
Verify transaction-before-report and canonical source-of-truth behavior for:
attendance, teacher attendance, sessions, grades, report artifacts, transcript.

### B. Lifecycle
Verify current lifecycle/state enforcement, including where applicable:
draft → submit/finalize → validate/lock → publish/correct.

Do not invent lifecycle states absent from implementation.

### C. RBAC and ownership
Verify real authorization paths for Wali Kelas, Waka Akademik, Super Admin and
any other currently implemented Academic role. Identify over-broad or missing
permissions.

### D. Audit/correction/versioning
Verify sensitive changes are traceable and post-lock/post-publication
corrections follow explicit workflows.

### E. Data quality
Verify missing/unresolved attendance, grade completeness, session/accounting
integrity, and other currently implemented Academic DQ checks.

### F. UI completeness
Inventory actual routes/views/forms/actions. Determine whether each major
workflow has a usable web path, not merely service-layer implementation.

Separate:
- backend complete / UI missing;
- UI present / backend incomplete;
- both complete.

### G. Reporting
Verify dashboards/exports use canonical semantic services rather than duplicate
formulas. Identify consumer-specific gaps.

### H. Test/evidence
Map critical capabilities to automated tests and note untested critical paths.

Foundation baseline:
exact final-head run `36484084952` is green.

The 506 warnings are non-blocking for entry to this review, but categorize them
only if they materially affect Academic completion; do not open a broad warning
cleanup task by default.

### I. Governance gaps
Preserve known unresolved governance, especially `SOC-MD-06` and any
domain-specific policy gates. Do not treat a governance-blocked feature as an
implementation defect.

### J. Operational usability
Assess whether a real Wali Kelas/Waka workflow can be completed end-to-end with
the current web surface using only authorized actions.

No live production data mutation is allowed.

## Required artifact

Create:

`codex/REVIEWS/ACADEMIC-WEB-COMPLETION-REVIEW-2026-09-29.md`

Required sections:

- executive completion statement;
- explicit review denominator;
- capability matrix;
- critical workflow matrix;
- role/UI matrix;
- backend-vs-UI gap matrix;
- governance blockers;
- technical gaps;
- deferred/out-of-scope items;
- exact test/evidence map;
- data-quality/audit/RBAC findings;
- completion percentage derived from the explicit denominator;
- production-readiness separation;
- prioritized next actions using dependency order;
- one recommended next atomic task or HOLD.

## Next-task rule

The review may recommend ONE next atomic task.

Prefer the smallest gap that:
- blocks an end-to-end Academic user workflow;
- has clear ownership;
- has evidence-defined scope;
- does not require production deployment.

If no implementation gap materially blocks Academic product completion, the
next step may be a governance decision/review rather than coding.

Do not route `IMP-S12-007` automatically.

## Allowed writes

Only:

- `codex/REVIEWS/ACADEMIC-WEB-COMPLETION-REVIEW-2026-09-29.md`
- `PROJECT_STATE.json`
- `EVIDENCE_INDEX.json`
- `TEST_MATRIX.csv`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`

## Forbidden

Do not modify:

- application source;
- routes/controllers/services/models;
- views/frontend assets;
- tests;
- migrations/schema;
- database/config runtime;
- provider/OpenAI state;
- pilot/staging/production data;
- deployment/hosting;
- Composer/PHP dependencies;
- main branch.

Do not merge.

## Acceptance criteria

- AWCR-AC-01 final foundation baseline/run reconciled.
- AWCR-AC-02 explicit in-scope requirement denominator created.
- AWCR-AC-03 all core Academic capabilities classified with exact evidence.
- AWCR-AC-04 critical workflows reviewed end-to-end.
- AWCR-AC-05 backend-vs-UI gaps explicitly separated.
- AWCR-AC-06 RBAC/audit/DQ/lifecycle evidence reviewed.
- AWCR-AC-07 role/user operational usability reviewed.
- AWCR-AC-08 completion percentage is denominator-derived, not estimated.
- AWCR-AC-09 governance-blocked vs technical gaps separated.
- AWCR-AC-10 production readiness kept separate from product completion.
- AWCR-AC-11 one next atomic task/HOLD proposed without implementation.
- AWCR-AC-12 no application/test/schema/database/deployment mutation.

## Closeout

Return:

`ACADEMIC_WEB_COMPLETION_REVIEW = COMPLETED / REVIEW_ONLY / PASS`

or HOLD if evidence is materially insufficient.

`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`

Commit/push review/evidence only, then STOP for ChatGPT audit.
