# Shared Parent Portal Boundary

Parent Portal is a shared guardian-facing consumer/application surface.

It owns portal-specific account-link/access/session-support metadata and access observability, while consuming:
- canonical Guardian/Student identity from Shared Core;
- auth/RBAC from Shared Platform;
- exact published parent-approved artifacts from source domains/reporting;
- notification/deep-link delivery from Shared Communication.

It does **not** own Student master, grades, attendance, Tahfizh facts, report authoring or WhatsApp provider transport.

Authoritative design: `docs/11_parent_portal/`.
