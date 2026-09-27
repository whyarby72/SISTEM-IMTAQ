# AI-A5 Recovery Checkpoint

Checkpoint: `AI-A5_20260920-110000`
Project: SISTEM IMTAQ
Date: 2026-09-20 Asia/Jakarta

This recovery checkpoint preserves the AI-A5 hardening result. Restore only the listed application/governance files after reviewing the change manifest. Do not restore `.env`, credentials, runtime cache, vendor, or database state from this checkpoint.

## Recovery boundary

- Application source changed only in the AI runtime, config, instructions, result envelope, and focused tests listed in `PRE_POST_SOURCE_HASHES.txt`.
- Database, migrations, seed/import, RBAC, academic business data, and pilot activation are unchanged.
- AI feature gate remains OFF.
- No live OpenAI request was made. `LIVE_OPENAI_SMOKE=NOT_RUN_CONFIGURATION_MISSING`.

## Evidence

- `PRE_POST_SOURCE_HASHES.txt` — post-change SHA-256 hashes for the changed source/test files.
- `VALIDATION_EVIDENCE.md` — commands and results.
- `RECOVERY_SHA256SUMS` — integrity hashes for checkpoint evidence.

