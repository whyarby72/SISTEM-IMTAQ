# AI-A5K-IR1 Recovery Checkpoint

Status: `CLOSED / ACCEPTED`  
Date: 2026-09-20 Asia/Jakarta

This checkpoint preserves the incident disposition, source-control guard, and evidence references. It intentionally excludes secrets, `.env`, database dumps, credentials, vendor content, and disposable PGDATA.

## Recovery boundary

- Do not rollback the pilot migration automatically.
- Keep AI provider credentials/configuration/pointer rows empty.
- Keep `academic.ai.assistant_enabled` false.
- Future migration writes must use `php artisan migrate:guarded <database> --host=<host> --port=<port>` and review the target output before continuing.
- The post-incident dump is stored outside the repository at `/tmp/ai-a5k-ir1-postincident-20260920/imtaq-post-incident.dump`.

## Evidence

- Incident report: `codex/INCIDENTS/AI-A5K-R1-TARGET-MISMATCH-2026-09-20.md`.
- Change manifest: `codex/CHANGE_MANIFESTS/AI-A5K-IR1-2026-09-20.md`.
- Source hashes: `SOURCE_HASHES.txt`.
- Validation evidence: `VALIDATION_EVIDENCE.md`.
- Evidence hashes: `RECOVERY_SHA256SUMS`.
