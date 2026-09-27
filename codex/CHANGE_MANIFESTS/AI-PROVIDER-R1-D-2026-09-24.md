# Change Manifest — AI-PROVIDER-R1-D

Project: SISTEM IMTAQ  
Task: Admin Observability, Secret Safety & Operational Security Hardening  
Date: 2026-09-24  
Status: `PASS / MANDATORY_SECURITY_REMEDIATION_COMPLETE`

## Completed

- Provider page reads `academic.ai.assistant_enabled` and displays Public Academic AI independently from provider runtime state.
- No public-AI activation control was added.
- Credential validation failure excludes `secret` from flashed old input.
- Credential verification/discovery/provider errors are normalized without raw provider bodies.
- Failed credential verification, model discovery, configuration verification/activation, and runtime operation events are audited with safe metadata.
- Credential events use `AiProviderCredential`; configuration events use `AiProviderConfiguration`; runtime events use `AiProviderActiveConfiguration` or canonical `AiProviderRuntime` for failures.
- Sensitive admin provider routes use a dedicated limiter: 12 requests per minute per authenticated user/IP.
- In-flight POST controls disable the submitting button and expose `aria-disabled` until navigation completes.
- Existing R1-B backend duplicate-DRAFT guard remains unchanged.
- Existing Super Admin backend authorization remains mandatory; Waka and Wali access remain denied.

## Required closeout values

```text
PUBLIC_AI_ADMIN_DISPLAY_USES_REAL_GATE = YES
AI_PROVIDER_PAGE_CAN_ENABLE_PUBLIC_AI = NO
SECRET_IN_SESSION_FLASH = NO
SECRET_IN_VALIDATION_OLD_INPUT = NO
SECRET_IN_LOG = NO
SECRET_IN_AUDIT = NO
SECRET_IN_ERROR_RESPONSE = NO
SECRET_IN_BROWSER_STORAGE = NO
SECRET_IN_URL = NO
SECRET_VALIDATION_FAILURE_BOUNDARY = PASS
SECRET_EXCEPTION_BOUNDARY = PASS
PROVIDER_ADMIN_ERROR_TAXONOMY = COMPLETE
FAILED_ADMIN_ACTIONS_AUDITED = YES
CREDENTIAL_EVENT_ENTITY_TYPE_CORRECT = YES
CONFIGURATION_EVENT_ENTITY_TYPE_CORRECT = YES
AUDIT_SECRET_PAYLOAD = NO
SENSITIVE_PROVIDER_ACTION_THROTTLE = IMPLEMENTED
CLIENT_INFLIGHT_DUPLICATE_SUBMISSION = PREVENTED_WHERE_APPLICABLE
RECENT_AUTH_REQUIRED = NOT_AVAILABLE_NONBLOCKING
SUPER_ADMIN_ACCESS = PASS
WAKA_ACCESS_DENIED = PASS
WALI_ACCESS_DENIED = PASS
UNAUTHENTICATED_ACCESS_DENIED = PASS
PROVIDER_ACTIVATION_AUTO_ENABLES_PUBLIC_AI = NO
FULL_CREDENTIAL_RETRIEVABLE_FROM_UI_API = NO
NEW_MIGRATION = NO
EXISTING_PROVIDER_DRAFTS_MUTATED = NO
LIVE_OPENAI_REQUEST = NO
REAL_OPENAI_API_KEY_USED = NO
REAL_STUDENT_DATA_SENT_EXTERNAL = NO
```

## Validation

- R1-D provider focused tests: 21 tests / 116 assertions, PASS.
- AI provider/runtime suite: 56 tests / 242 assertions, PASS.
- Academic suite: 387 tests / 1,585 assertions, PASS.
- Full suite: 500 tests / 2,053 assertions, PASS.
- PHP lint: PASS.
- Pint: PASS.
- View cache: PASS.
- Live OpenAI: NOT RUN.

## Safety

- No migration or schema change.
- No historical AuditLog rewrite.
- No credential/configuration activation.
- No public Academic AI activation.
- Two existing DRAFT rows untouched.

## Gate

`MANDATORY_SECURITY_REMEDIATION = COMPLETE`  
`NEXT_ATOMIC_TASK = RETURN_TO_CHATGPT_FOR_R1D_AUDIT`
