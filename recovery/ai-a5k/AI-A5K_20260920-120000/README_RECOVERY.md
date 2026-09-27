# AI-A5K Recovery Checkpoint

Status: `BLOCKED / SAFE CHECKPOINT`
Date: 2026-09-20 Asia/Jakarta

This checkpoint preserves the AI-A5K source implementation and evidence without copying any database dump, API key, `.env`, credential, cache, or vendor content into the repository.

## Restore boundary

- Restore only files listed in `SOURCE_HASHES.txt` after reviewing `codex/CHANGE_MANIFESTS/AI-A5K-2026-09-20.md`.
- The AI-A5K migration was later found present on the pilot during an execution-target incident; do not rollback it automatically. See `codex/INCIDENTS/AI-A5K-R1-TARGET-MISMATCH-2026-09-20.md` and the AI-A5K-IR1 recovery checkpoint for ratification evidence.
- Public AI remains OFF; pilot activation and AI-A5V are not started.
- Legacy env provider configuration remains compatibility-only and is not a runtime fallback.

## Evidence

- `SOURCE_HASHES.txt` — post-change hashes for source/test files.
- `VALIDATION_EVIDENCE.md` — redacted validation results and migration gate disposition.
- `RECOVERY_SHA256SUMS` — checkpoint evidence hashes.
