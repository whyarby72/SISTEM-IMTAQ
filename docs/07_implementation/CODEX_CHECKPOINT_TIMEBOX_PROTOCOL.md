# Codex Checkpoint Choice & Timebox Protocol v1.0

**Status:** `DESIGN_LOCKED`  
**Applies to:** Codex implementation/maintenance sessions in this repository  
**Purpose:** let the business owner control how long the MacBook needs to remain available without needing to understand source files or predict total task duration.

## 1. Core rule
Codex must **not assume it may continue indefinitely through the whole task**.

After completing one coherent atomic step, Codex must reach a safe checkpoint and present a **Checkpoint Menu** for the next work segment. The owner chooses how far Codex should continue.

This protocol controls **session horizon**, not project/task progress. Task progress still comes from task evidence and `PROJECT_PROGRESS.md`, never elapsed time.

## 2. Estimates are ranges, not guarantees
Checkpoint durations are estimates based on the next known work, for example:
- `QUICK`: about 5–10 minutes;
- `STANDARD`: about 15–25 minutes;
- `EXTENDED`: about 30–60 minutes.

Codex may use different ranges when the actual next atomic steps require them. Do not promise an exact finish time. State uncertainty when dependencies, downloads, test suites, network operations or environment problems can expand duration.

If no genuinely safe ~5-minute checkpoint exists, **do not invent one**. Say that the shortest safe option is longer and explain why.

## 3. Required Checkpoint Menu
At every safe checkpoint, Codex must show 2–4 numbered options. Option **1 remains the primary recommendation** under repository rules.

Each execution option must include:
1. estimated time range;
2. exact intended outcome/checkpoint;
3. major commands/operations expected, at a business-readable level;
4. whether the step may involve network/download/build/test/migration work;
5. estimate confidence: `HIGH`, `MEDIUM`, or `LOW`.

Also include a stop option when the current state is safe to close.

Example:

```text
NEXT CHECKPOINT OPTIONS

1. RECOMMENDED — STANDARD (~15–25 min, confidence MEDIUM)
   Finish Laravel scaffold + baseline config, run smoke checks, then stop.

2. QUICK (~5–10 min, confidence HIGH)
   Create/verify repository directories and environment templates only, then stop.

3. EXTENDED (~35–50 min, confidence LOW)
   Do option 1 plus dependency/test bootstrap if downloads succeed, then stop.

4. STOP NOW
   Current state is SAFE_TO_CLOSE = YES.
```

The owner may simply reply `1`, `2`, `3`, or `4`.

## 4. Selected horizon behavior
When the owner chooses an option, Codex should execute only the stated scope.

Before starting each subsequent sub-step within that horizon, Codex must ask internally:
- Is this sub-step necessary for the selected checkpoint?
- Is it reasonably likely to finish inside the chosen horizon?
- Can it be interrupted safely if it takes longer?

If the answer is uncertain, do not start the risky sub-step near the planned checkpoint. Record it as the next option instead.

The time range is a planning target, **not a hard real-time timer**. If an already-started command takes longer than expected, do not corrupt/cancel an unsafe operation merely to hit the estimate. Finish or recover the operation safely, then report the overrun and checkpoint.

## 5. Safe checkpoint definition
`SAFE_TO_CLOSE = YES` may be reported only when:
- no required foreground command is still running;
- no package install/build/import/migration/restore is mid-operation;
- no destructive or half-applied database operation is pending;
- edited files are persisted to disk;
- current Git branch/status is captured;
- exact resume point is written to `codex/WORK_LOG.md` or the active task notes;
- test/check status is recorded (`PASS`, `FAIL`, or `NOT_YET_RUN` with reason);
- next intended step is recorded;
- no user action is required before safe machine sleep/shutdown.

A clean Git commit is preferred when the checkpoint forms a coherent commit. If the work is intentionally still WIP, saved worktree state plus explicit `git status`/resume notes is acceptable; do not create misleading “complete” commits for incomplete behavior.

`SAFE_TO_CLOSE = NO` must state the active reason, for example `composer install running`, `migration in progress`, or `database restore active`.

## 6. Required checkpoint report
At a checkpoint, report:

```text
CHECKPOINT STATUS
Task: IMP-...
Atomic step completed: ...
Task progress: ...% (from repository evidence)
Running process: NONE / ...
Git state: CLEAN / SAVED_WIP / ...
Tests: PASS / FAIL / NOT_YET_RUN
SAFE_TO_CLOSE: YES / NO
Resume point: ...
```

Then show the next checkpoint choices.

## 7. Emergency checkpoint
If the owner says `SAFE CHECKPOINT NOW`, `saya mau tutup MacBook`, or equivalent:
1. do not start new work;
2. safely finish or recover only the operation already in progress;
3. persist files/status/logs;
4. record the resume point;
5. report `SAFE_TO_CLOSE = YES/NO`;
6. stop and wait.

Do not launch a new test suite, package upgrade, migration, data import or refactor merely to make the checkpoint “cleaner”.

## 8. Resume after interruption
On resume:
1. read repository status files and `codex/WORK_LOG.md`;
2. inspect Git branch/status;
3. verify no partial database/dependency operation needs recovery;
4. restate the last safe checkpoint;
5. continue only the previously selected or newly selected checkpoint horizon.

Use `handoff/01_PROMPTS/03_RESUME_AFTER_INTERRUPTION.txt` for a fresh Codex chat.

## 9. Long-running operations
For operations whose duration is inherently unpredictable (large dependency download, full regression suite, bulk import, backup/restore):
- disclose this before starting;
- offer a shorter checkpoint before the operation where possible;
- label the option confidence `LOW` when appropriate;
- never promise that closing/sleeping the local MacBook is safe while the local process is still running.

## 10. User-friendly operating principle
The owner controls **how much time to allocate**. Codex controls **which technical sub-steps fit safely inside that allocation**.

The owner is never required to identify source files, terminal commands, migrations or deployment files.
