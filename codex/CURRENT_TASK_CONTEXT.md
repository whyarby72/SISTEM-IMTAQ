# CURRENT TASK CONTEXT

**Task:** `ACADEMIC_WEB_COMPLETION_REVIEW`  
**State:** `COMPLETED / REVIEW_ONLY / PASS`
**Current phase:** `ACADEMIC WEB / COMPLETION REVIEW`  
**Branch:** `chore/academic-web-completion-review-routing`  
**State-basis:** `dfaf585c299aa83fccef7be95d746f943242c192`

## Foundation entry gate

Foundation stabilization is CLOSED / PASS.

Exact final-head evidence:
- run: `36484084952`
- head: `dfaf585c299aa83fccef7be95d746f943242c192`
- PostgreSQL 18.6: PASS
- UTC technical session: PASS
- test DB identity guard: PASS
- migration-from-zero: PASS
- schema/extension checks: PASS
- foundation suite: `15 passed, 506 warnings, 2202 assertions, 0 failed`
- verify-foundation: PASS
- Today TZ-B boundary: RESOLVED
- CI disposable credential visibility: RESOLVED

## Review objective

Determine the actual, evidence-backed completion state of the Academic Web
product before any new implementation sprint is authorized.

Primary scope:
- student attendance;
- teacher attendance;
- teaching schedule/session lifecycle;
- substitution/swap/reschedule/cancellation;
- semester grades;
- report card;
- transcript/academic history;
- operational/management views and exports;
- RBAC, audit trail, correction/locking, data quality;
- responsive web usability for current authorized Academic roles.

## Important distinction

This review is product-completion review, NOT production-cutover readiness.

`IMP-S12-007 Production cutover readiness review` remains NOT_STARTED and must
not be self-activated.

Canonical queue gate remains:
`SOC-MD-06`.

Public Academic AI remains OFF.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WEB-COMPLETION-REVIEW.md`

## Boundary

REVIEW ONLY.

No source/test/schema/migration/database/provider/deployment mutation.

## Exit

Create the review artifact, reconcile evidence/state only, propose the smallest
next task or explicit governance HOLD, commit/push, then STOP for ChatGPT audit.

Review artifact created: `codex/REVIEWS/ACADEMIC-WEB-COMPLETION-REVIEW-2026-09-29.md`.
Derived completion: `4 / 10 = 40%`.
Recommended next atomic task: `ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN`.
