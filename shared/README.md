# Shared SISTEM IMTAQ Workstreams

Shared areas provide reusable platform capabilities to domain modules. They do not own domain-specific educational facts merely because they are shared.

- `core/` — canonical identity/auth/audit/common master contracts.
- `platform/` — runtime/infrastructure/notifications/jobs/backup/logging conventions.
- `reporting/` — future cross-domain semantic/Student 360/reporting consumer layer.
- `ai/` — future optional AI provider/orchestrator/tools/evaluation layer.

Shared Core changes are high-impact and require change-impact analysis across active modules.

- `communication/` — future shared recipient/template/queue/provider/delivery layer for WhatsApp and other channels.

## Parent Portal
`shared/parent_portal/` is the Guardian-facing authenticated consumer surface. It consumes Shared Core identities, Shared Platform security and exact published parent-approved artifacts; it owns no source-domain facts.
