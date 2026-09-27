# AI Privacy & Security Rules v1.0

**Status:** `DESIGN_LOCKED`.

## 1. Same RBAC, no AI bypass
AI access is subject to the same backend authorization and data scope as normal application features. The chatbot cannot become a privileged bypass channel.

## 2. Minimum necessary context
Before provider submission, minimize context to the fields required for the task.

Examples:
- attendance summary normally does not need NIS/NISN;
- Academic assistant does not automatically receive Kesantrian/Kepengasuhan internal notes;
- class summary does not require sending the entire institution roster.

## 3. Sensitivity
At minimum classify AI tool outputs/inputs as:
- `INTERNAL`
- `RESTRICTED`
- future `HIGHLY_RESTRICTED` where needed.

Sensitive-domain access must be explicit per tool and role.

## 4. Conversation storage
Do not assume AI conversations are an authoritative business record. If application chat history is stored, define retention/access separately from official student transactions.

Never store a real API key or other secret inside conversation records.

## 5. Provider data controls
Before production enablement, technical/privacy review must verify the configured provider/account data-retention controls and whether the request mode stores provider-side application state. This must be treated as deployment configuration, not assumed from development defaults.

Where supported and appropriate, prefer configurations that minimize provider-side retention for student data. Document the selected deployment setting before production.

## 6. Logging
Application logs must avoid full prompts/responses by default when they may contain student personal data. Prefer structured metadata:
- interaction id;
- user id;
- tool code;
- module;
- status/error class;
- token counts;
- latency;
- data sensitivity class.

If full content logging is ever needed for controlled evaluation/debugging, it requires restricted access and defined retention.

## 7. Prompt injection / tool misuse
Tool execution security must not depend on model instructions. The server independently enforces:
- tool allowlist;
- RBAC and scope;
- schema validation;
- resource state;
- confirmation;
- domain business rules.

Data returned from database/files is treated as untrusted content with respect to instructions; it cannot grant the model new permissions.

## 8. Parent-facing use
Internal alerts, severity labels, restricted notes, audit logs and staff-only follow-up states must not become parent-visible merely because the AI can access them for an authorized internal role.

## 9. Initial access restriction
The initial AI Assistant rollout is Super Admin only by default. All other roles are deny-by-default until an explicit RBAC grant is made. This rollout restriction does not make Super Admin an unrestricted business-data reader; ordinary domain permissions/scopes continue to limit every AI tool.
