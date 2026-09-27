# Change Manifest — AI-PROVIDER-UX-S1

Date: 2026-09-25
Status: COMPLETED / PASS
Scope: Super Admin AI Provider presentation only.

## Changes

- Reorganized `application/web/resources/views/admin/system/ai-provider/index.blade.php` into a primary API Key → Model AI → save-and-test workflow.
- Added collapsed `Pengaturan Lanjutan` for runtime, credential history, and configuration history.
- Preserved existing routes, forms, CSRF, server-side discovery, stale-discovery protection, in-flight submit protection, RBAC, lifecycle, verification, activation, secret handling, and public-AI read-only status.
- Updated `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php` presentation assertions and added simplified-workflow coverage.

## Unchanged boundaries

No controller/service redesign, migration, schema change, database write, credential/configuration mutation, existing DRAFT mutation, provider verification/activation, OpenAI request, public AI activation, Academic business-data change, attendance semantic change, or RBAC change.

## Validation

- Provider focused: 25 tests / 144 assertions — PASS
- JavaScript submit guard: 2 / 2 — PASS
- Academic: 391 tests / 1,613 assertions — PASS
- Full PHP suite: 504 tests / 2,081 assertions — PASS
- `php artisan view:cache` — PASS
- `vendor/bin/pint --test` — PASS
- PHP lint — PASS

## Gate

`GATE_B_AFTER_UXS1=READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`

`NEXT_ATOMIC_TASK=RETURN_TO_CHATGPT_FOR_UXS1_AUDIT`
