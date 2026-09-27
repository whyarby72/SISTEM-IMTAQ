# Recovery Checkpoint — AI-PROVIDER-INPUT-P1

This checkpoint contains the minimal continuation-envelope and safe error-taxonomy patch after D4.

Restore/revert only the two listed source files if this checkpoint is rejected. Do not alter provider records, active pointers, credentials, or schema.

Validation:

- provider/P1 tests: PASS
- AI provider suite: PASS
- Academic suite: PASS
- full suite: PASS
- PHP lint, Pint, and view cache: PASS
- live OpenAI: NOT RUN
- provider state mutation: NONE

Hashes are recorded in `SOURCE_HASHES.sha256`.
