# Safe Checkpoint Checklist

Before reporting `SAFE_TO_CLOSE = YES`, confirm:

- [ ] No required foreground process is running.
- [ ] No package install/build/import/migration/restore is mid-operation.
- [ ] No destructive/half-applied DB operation is pending.
- [ ] Edited files are saved to disk.
- [ ] Git branch and `git status` are captured.
- [ ] Coherent checkpoint commit exists when appropriate, otherwise SAVED_WIP is explicitly recorded.
- [ ] Test/check state is recorded as PASS / FAIL / NOT_YET_RUN.
- [ ] `codex/WORK_LOG.md` or active-task notes contain exact resume point.
- [ ] Next intended atomic step is recorded.
- [ ] No user action is required before machine sleep/shutdown.
- [ ] Checkpoint report includes `SAFE_TO_CLOSE = YES`.
- [ ] Next checkpoint menu contains 2–4 numbered choices with time ranges and confidence.
