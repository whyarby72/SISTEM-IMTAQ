# Recovery Checkpoint — AI-PROVIDER-DIAG-D3

This checkpoint records the D3 source/test boundary after validation.

Restore/revert only the two listed source files if a later audit rejects this checkpoint. Do not alter provider records, active pointers, credentials, or database schema.

Validation at checkpoint:

- focused provider tests: PASS
- Academic suite: PASS
- full suite: PASS
- PHP lint, Pint, and view cache: PASS
- live OpenAI: NOT RUN
- database/application runtime mutation: NONE

The source hashes are recorded in `SOURCE_HASHES.sha256`.
