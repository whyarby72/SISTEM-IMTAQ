# Recovery Checkpoint — AI-PROVIDER-INPUT-P2

This checkpoint records the minimal verification-only input compatibility patch after M1.

Restore/revert only the two listed source files if this checkpoint is rejected. Do not alter provider records, active pointers, credentials, or schema.

Validation:

- P2/provider tests: PASS
- AI provider suite: PASS
- Academic suite: PASS
- full suite: PASS
- PHP lint, Pint, and view cache: PASS
- live OpenAI retry: NOT RUN
- provider state mutation: NONE

Hashes are recorded in `SOURCE_HASHES.sha256`.
