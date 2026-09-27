# Change Manifest — AI-PROVIDER-R1-A

Project: SISTEM IMTAQ  
Task: Provider Verification & Lifecycle Integrity  
Date: 2026-09-24  
Status: `PASS / BLOCKED_PENDING_R1B`

## Scope

- Replaced configuration verification by HTTP-2xx assurance with a synthetic function roundtrip.
- Enforced configuration verification source state `DRAFT` only.
- Rejected `ACTIVE`, `SUPERSEDED`, and revoked-credential verification.
- Added safe provider failure taxonomy and failed-verification audit events.
- Added fake-provider tests for synthetic `NOT_FOUND`, matching call ID handback, final completion, state guards, and failure categories.

## Safety

- New migration: `NO`.
- Database schema change: `NO`.
- Existing two runtime DRAFT rows mutated: `NO`.
- Provider activation: `NO`.
- Public AI activation: `NO`.
- Live OpenAI request: `NO`.
- Real OpenAI key used: `NO`.
- Real Academic/student data sent externally: `NO`.
- Academic business write: `NO`.
- Model-binding remediation: `NO` (deferred to R1-B).

## Verification contract

The configuration verifier now requires exactly five strict list-valued tool schemas, a single `resolve_student` call, a non-empty call ID, the exact synthetic token, resolver status `NOT_FOUND`, a matching `function_call_output` call ID, and final text exactly `IMTAQ_SYNTHETIC_VERIFICATION_COMPLETE`.

Failed verification leaves the configuration status unchanged and appends only safe failure metadata.

## Validation

- R1-A focused provider tests: 13 tests / 66 assertions, PASS.
- AI suite: 44 tests / 176 assertions, PASS.
- Academic suite: 375 tests / 1,519 assertions, PASS.
- Full suite: 488 tests / 1,987 assertions, PASS.
- PHP lint: PASS.
- Pint: PASS.
- View cache: PASS.

## Gate

`GATE_B_AFTER_R1A = BLOCKED_PENDING_R1B`.

Provider activation remains blocked because model-to-credential binding and duplicate-DRAFT integrity are not in R1-A scope.

## Source hashes

- `application/web/app/Domains/Academic/AI/Exceptions/AiProviderVerificationException.php`: `773c916e78a4ade3d8bad56e274fb710a0bdd1cbd03756797df8a7afb9dcb330`
- `application/web/app/Domains/Academic/AI/Services/AiProviderConfigurationService.php`: `d85ffda1b15bd1848261eb51ec561a595f0a2dc603a50f176977992dc38c61df`
- `application/web/app/Http/Controllers/Admin/AiProviderConfigurationController.php`: `cf3363582984d8b0a994f3fdc38960196cab7a80069fcdac37fd67d47258d8fa`
- `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php`: `bd36a6781912a03800fd8b25170c75fde41ea2393a0b33c8e762b84db9578405`
