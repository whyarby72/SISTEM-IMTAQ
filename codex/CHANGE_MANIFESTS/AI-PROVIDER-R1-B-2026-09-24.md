# Change Manifest — AI-PROVIDER-R1-B

Project: SISTEM IMTAQ  
Task: Credential / Model Binding & Duplicate-DRAFT Integrity  
Date: 2026-09-24  
Status: `PASS / READY_FOR_CONTROLLED_PROVIDER_VERIFICATION`

## Scope

- Server-side re-discovery on configuration submit using the selected credential.
- Reject arbitrary models not returned for that credential.
- Reject equivalent open DRAFTs using the equality key provider + credential_id + model + max_output_tokens.
- Add PostgreSQL transaction advisory locking for race-safe application-layer duplicate prevention without a migration.
- Add stale discovery response protection with `AbortController` and generation/credential identity checks.
- Display safe DRAFT distinction metadata: credential label, creation time, and short configuration ID.
- Normalize model discovery failures without raw provider response bodies.

## Safety

- New migration: `NO`.
- Database schema change: `NO`.
- Existing two duplicate DRAFT rows mutated: `NO`.
- Existing DRAFTs verified/activated/superseded/merged: `NO`.
- Provider activation: `NO`.
- Public AI activation: `NO`.
- Live OpenAI request: `NO`.
- Real OpenAI key used: `NO`.
- Real Academic/student data used: `NO`.
- Academic business write: `NO`.
- R1-C started: `NO`.

## Binding design

The backend performs server-side re-discovery at submit time. It decrypts and uses only the selected credential, obtains the current model list, and accepts the submitted model only when it is an exact member of that result. Browser dropdown state is not authoritative.

## Duplicate design

Equivalent open DRAFT equality key:

```text
provider | credential_id | model | max_output_tokens
```

The service rejects an equivalent DRAFT. On PostgreSQL it acquires a transaction advisory lock derived from the equality key before checking/creating the row, then uses `lockForUpdate()` on any existing row. No database constraint or migration was created.

## Validation

- R1-B focused provider tests: 17 tests / 84 assertions, PASS.
- AI suite: 48 tests / 194 assertions, PASS.
- Academic suite: 379 tests / 1,537 assertions, PASS.
- Full suite: 492 tests / 2,005 assertions, PASS.
- PHP lint: PASS.
- Pint: PASS.
- View cache: PASS.

## Gate

`GATE_B_AFTER_R1B = READY_FOR_CONTROLLED_PROVIDER_VERIFICATION`.

This does not authorize activating any configuration. Public Academic AI remains OFF and R1-C findings remain open.

## Source hashes

- `application/web/app/Domains/Academic/AI/Exceptions/AiProviderDiscoveryException.php`: `cc40e323b93ef4fef2c798c1a13b97813947187d2cecc95e89485ddd77b1d549`
- `application/web/app/Domains/Academic/AI/Services/AiProviderConfigurationService.php`: `6c667824011e5b452d5d557c12bbf4b8ccacd129446da0276f7ecc48715ba212`
- `application/web/app/Http/Controllers/Admin/AiProviderConfigurationController.php`: `3196d2dcb2a2bdac306016bdc1ecfd7b7e7fbd77af4a42faec9c04eddc595d7c`
- `application/web/resources/views/admin/system/ai-provider/index.blade.php`: `7affb9c9c15d5f218ee26cb2a8c8e6da2d83c2b1c103856e12c98964af168103`
- `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php`: `52e23e378c396cc20eead24f744afa111c41a2c1b82533603eee2acebb3b398c`
