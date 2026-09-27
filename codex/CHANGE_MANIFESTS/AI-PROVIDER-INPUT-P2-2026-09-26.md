# Change Manifest — AI-PROVIDER-INPUT-P2

Project: SISTEM IMTAQ  
Task: Deterministic Verification Input Compatibility Patch  
Date: 2026-09-26  
Status: `PASS`

## Changes

- Changed only the synthetic provider verification initial input to a one-item Responses message list.
- Forced verification-only `tool_choice` to the canonical `resolve_student` function while keeping ordinary Academic runtime policy `auto`.
- Preserved five canonical strict tools, synthetic `NOT_FOUND` semantics, and the P1 list-shaped continuation.
- Updated fake HTTP fixtures and executable contract assertions for the verification envelope.

## Safety

- No live OpenAI request or real API key use.
- No migration, schema change, provider/DRAFT mutation, active-pointer change, runtime toggle, Public AI activation, or Academic data external processing.
- Safe D3 error boundary and P1 `INVALID_INPUT_TYPE` taxonomy remain intact.

## Validation

- P2/provider focused: 30/30 tests, 243 assertions — PASS.
- AI provider suite: 65/65 tests, 369 assertions — PASS.
- Academic suite: 396/396 tests, 1,712 assertions — PASS.
- Full suite: 509/509 tests, 2,180 assertions — PASS.
- PHP lint, Pint, and Blade view cache: PASS.

## Status

`CONTROLLED_VERIFICATION_RETRY_READINESS = READY`  
`INITIAL_LIVE_INPUT_REJECTION_ROOT_CAUSE = NOT_RETRIED / REQUIRES SEPARATE CONTROLLED RETRY`  
`NEXT_ATOMIC_TASK = RETURN_TO_CHATGPT_FOR_INPUT_P2_AUDIT`
