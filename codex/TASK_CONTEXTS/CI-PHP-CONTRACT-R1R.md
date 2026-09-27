# CI-PHP-CONTRACT-R1R — Runtime Constraint Precision Correction

State: READY_FOR_EXECUTION
Owner: CODEX
Branch: chore/ci-php-r1r-constraint-correction
State-basis: 1b4b1caf16635d0331651db30abc7e730d49267d

Purpose:
Correct the R1 design so the Composer PHP constraint exactly matches the owner-approved runtime authority.

Owner-approved runtime:
- PHP family: 8.4.x
- minimum: 8.4.1

Required correction:
- replace proposed composer.json constraint ^8.4.1 with ~8.4.1
- rationale: ~8.4.1 means >=8.4.1 and <8.5.0, matching the approved 8.4.x family
- preserve CI target design: PHP 8.4 with executable minimum-version assertion
- preserve R2 as the next implementation task

Also reconcile repository state:
- PROJECT_STATE.json acceptance_ids must be R1R-specific, not D2 acceptance IDs
- current_task_id must be CI-PHP-CONTRACT-R1R
- next_task remains CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84 after closeout
- NEXT_ACTION.md and CURRENT_TASK_CONTEXT.md must reflect R1R during execution and R2 after PASS

Allowed writes:
- codex/PLANS/CI-PHP-CONTRACT-R1-ALIGN-RUNTIME-UPWARD-DESIGN-2026-09-27.md
- PROJECT_STATE.json
- codex/CURRENT_TASK_CONTEXT.md
- NEXT_ACTION.md
- EVIDENCE_INDEX.json only if needed for factual consistency

Forbidden:
- .github/workflows/application-foundation.yml
- application/web/composer.json
- application/web/composer.lock
- application source
- dependency/runtime changes
- database/migrations
- provider/OpenAI
- deployment

Acceptance:
- R1R-AC-01 plan uses ~8.4.1, not ^8.4.1
- R1R-AC-02 owner runtime remains PHP 8.4.x minimum 8.4.1
- R1R-AC-03 CI target remains 8.4 plus minimum assertion
- R1R-AC-04 no implementation changes
- R1R-AC-05 PROJECT_STATE acceptance_ids no longer carry D2 IDs
- R1R-AC-06 next task is CI-PHP-CONTRACT-R2-ALIGN-CI-TO-PHP84
- R1R-AC-07 commit/push clean

Closeout:
CI-PHP-CONTRACT-R1R = COMPLETED / PASS
STOP for ChatGPT audit.
