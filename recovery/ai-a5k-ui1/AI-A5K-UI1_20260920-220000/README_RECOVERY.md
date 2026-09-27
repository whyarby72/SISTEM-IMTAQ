# AI-A5K-UI1 Recovery Checkpoint

Status: `CLOSED / ACCEPTED`  
Date: 2026-09-20 Asia/Jakarta

## Restore boundary

- Restore only the files listed in `SOURCE_HASHES.txt` after reviewing the UI1 change manifest.
- Keep provider credentials/configurations/active pointers empty.
- Keep the public AI feature OFF.
- Do not start AI-A5V or AI-A6.
- No migration or database restore is part of this checkpoint.

## Evidence

- `SOURCE_HASHES.txt` records the UI1 PHP and Blade source files.
- `VALIDATION_EVIDENCE.md` records route/RBAC/empty-state and regression evidence.
- No `.env`, credential, secret, database dump, or runtime cache is included.
