# SOC-I1C Recovery Checkpoint

Date: 2026-09-19
Task: Canonical Session Occurrence Workflow & RBAC Integration
Mode: Implemented behind disabled cutover gate

This checkpoint contains post-change copies of every application file changed by SOC-I1C. The repository is not a Git worktree, so these copies and the SHA-256 manifest are the rollback baseline for this atomic step.

No migration, seed/import, pilot occurrence write, historical rewrite, or `.env`/credential file is included.

Restore procedure: review `SOURCE_MANIFEST.md`, verify `RECOVERY_SHA256SUMS.txt`, then restore only the reviewed source files from `source/` using the repository's normal controlled release process.

