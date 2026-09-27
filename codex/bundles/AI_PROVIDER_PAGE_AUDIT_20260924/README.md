# SISTEM IMTAQ — AI Provider Page Audit Bundle

Created: 2026-09-24 Asia/Jakarta  
Scope: `/admin/system/ai-provider`

This bundle is for external audit of the Super Admin AI Provider settings page, from Blade/frontend through route/controller/service/model/migration and the AI runtime contracts used by configuration verification.

## Included

- AI Provider Blade page and shared sidebar used for navigation.
- Web routes and Super Admin controller.
- Complete `app/Domains/Academic/AI` source needed to inspect provider configuration, Responses transport, typed tools, identity/data readers, and runtime orchestration.
- Related request/controller wiring, service configuration, and AppServiceProvider bindings.
- AI provider migration only.
- AI feature and provider tests.
- Relevant change manifests, recovery evidence, current task context, and next-action pointer.
- `RUNTIME_OBSERVATION.md` with non-secret metadata observed on the local page.

## Explicitly excluded

- `.env`, API keys, credentials, passwords, tokens, cookies, and session data.
- Database dumps or real database exports.
- `vendor/`, cache, logs, compiled views, uploads, and runtime artifacts.
- Unrelated application modules and unrelated migrations.

## Important runtime state

- Public AI remains OFF.
- Runtime active pointer is not activated by this bundle operation.
- The two DRAFT configuration records described in `RUNTIME_OBSERVATION.md` were inspected read-only; no record was deleted or modified.

## How to audit locally

From `application/web/`:

```bash
php artisan test tests/Feature/Academic/AI
php artisan view:cache
```

Do not run migrations, seeders, imports, or destructive database commands against a real-data database for this audit.
