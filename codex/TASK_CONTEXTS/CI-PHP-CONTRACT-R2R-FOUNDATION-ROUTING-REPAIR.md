# CI-PHP-CONTRACT-R2R — Foundation Routing Repair

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** NARROW_REPOSITORY_ROUTING_REPAIR  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** chore/ci-php-r2r-foundation-routing-repair  
**State-basis before R2R:** 523ddc3030c539eb98d0a0388affac7aa060758f

## Verified facts

Exact R2 implementation commit:
`e4387589a60c1e6609536d22b262bc7ba0ffbdaf`

Exact GitHub Actions run:
`36353770932`

Verified run behavior:
- Set up PHP: PASS
- PHP range guard >=8.4.1 and <8.5.0: PASS
- Composer validate: PASS
- Install locked dependencies: PASS
- Run foundation verification: FAIL

Therefore:
- `CI_PHP_LOCKFILE_COMPATIBILITY` is substantively RESOLVED.
- Exact CI is VERIFIED as FAILING on a new independent repository-routing blocker.
- The new blocker is `FOUNDATION_ROUTING_NEXT_ACTION_ID`.

Current structure checker expects:
`**Next task ID:** `<task-id>``

and requires that task id to exist in `codex/TASK_QUEUE.md`.

The current canonical queue gate in `codex/TASK_QUEUE.md` is:
`SOC-MD-06`.

## Goal

Repair the repository-routing compatibility that causes foundation verification to fail, then obtain a fresh exact-current GitHub Actions result.

This task must not modify PHP/Composer/dependencies/application business source/database/provider/deployment.

## Allowed writes

1. `NEXT_ACTION.md`
   - add/restore a canonical queue-compatible line:
     `**Next task ID:** `SOC-MD-06``
   - preserve the separate atomic maintenance routing for R2R/R2 evidence as needed.
   - do not invent a queue task that does not exist.

2. `.github/workflows/application-foundation.yml`
   - add `NEXT_ACTION.md` to both push and pull_request path filters because foundation verification consumes it.
   - do not modify PHP version, runtime guard, Composer steps, or verification command.

3. Evidence/state only:
   - `codex/CHANGE_MANIFESTS/CI-PHP-CONTRACT-R2R-2026-09-28.md`
   - `PROJECT_STATE.json`
   - `TEST_MATRIX.csv`
   - `EVIDENCE_INDEX.json`
   - `codex/CURRENT_TASK_CONTEXT.md`
   - `NEXT_ACTION.md`

## Required sequence

1. Verify exact branch/HEAD/upstream/worktree.
2. Confirm `SOC-MD-06` exists in `codex/TASK_QUEUE.md`.
3. Repair `NEXT_ACTION.md` with the exact queue-compatible marker.
4. Add `NEXT_ACTION.md` to workflow path filters.
5. Run:
   - `python3 scripts/check_project_structure.py`
   - any other minimal static verification needed for the routing change.
6. Commit/push implementation.
7. Wait for or inspect the GitHub Actions run for the exact repair commit.
8. Verify the workflow reaches and passes `Run foundation verification`.
9. If CI passes:
   - mark `FOUNDATION_ROUTING_NEXT_ACTION_ID = RESOLVED`;
   - mark `GITHUB_ACTIONS_EXACT_COMMIT_UNVERIFIED = RESOLVED/STALE`;
   - mark `CI_PHP_LOCKFILE_COMPATIBILITY = RESOLVED`;
   - record exact run URL/id and exact commit.
10. If another independent failure appears, record it exactly and STOP; do not hide it.

## Important evidence correction

The previous R2 closeout said exact GitHub Actions could not be verified because local `gh` auth was invalid. That statement is now stale as a repository-wide fact: ChatGPT directly verified exact run `36353770932` through the connected GitHub integration.

R2R must update durable evidence so it says:
- local `gh` CLI auth was invalid for Codex;
- exact R2 Actions evidence was subsequently verified externally through the connected GitHub integration;
- the run failed only at foundation routing after PHP/Composer install had passed.

Do not claim the R2 run itself passed.

## Forbidden

Do not:
- change `application/web/composer.json`;
- change `application/web/composer.lock`;
- change PHP/runtime target;
- run Composer update;
- change dependency versions;
- change application/business source;
- change database/migrations;
- change provider/OpenAI state;
- deploy;
- modify hosting;
- merge to main;
- rename or redefine `SOC-MD-06`;
- expand into a broad routing refactor.

## Acceptance criteria

- R2R-AC-01 exact baseline state recorded.
- R2R-AC-02 `SOC-MD-06` confirmed present in task queue.
- R2R-AC-03 `NEXT_ACTION.md` contains exact `**Next task ID:** `SOC-MD-06`` marker.
- R2R-AC-04 workflow path filter includes `NEXT_ACTION.md`.
- R2R-AC-05 PHP/Composer/runtime guard unchanged from R2.
- R2R-AC-06 structure checker passes locally.
- R2R-AC-07 exact repair-commit GitHub Actions result recorded.
- R2R-AC-08 if CI passes, all three stale/resolved blocker states are reconciled accurately.
- R2R-AC-09 no application/database/provider/deployment mutation.
- R2R-AC-10 remote parity PASS and worktree clean.

## Closeout

Preferred:
`CI-PHP-CONTRACT-R2R = COMPLETED / PASS`

Then next product track:
`ACADEMIC_WEB_COMPLETION_REVIEW`

The canonical queue gate remains `SOC-MD-06`; do not silently bypass that governance gate for source implementation.

STOP for ChatGPT audit after closeout.
