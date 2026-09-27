# Bundle Contents — v1.16

The bundle intentionally preserves the **complete** project knowledge base:
- governance and IMTAQ Core Engine baseline;
- Academic design/implementation contracts;
- Shared Core, RBAC, audit, privacy and database contracts;
- schedule conflict engine, testing/UAT, migration;
- safe change, staging, rollback and checkpoint protocols;
- future OpenAI AI, Communication/WhatsApp and Parent Portal designs;
- eight-domain registry/roadmap;
- task/project/progress controls, templates and validation scripts;
- complete handoff/prompt/non-programmer guides.

v1.16 adds a separate **small active-context layer** so Codex does not read the complete knowledge base on every turn:
- `codex/CURRENT_TASK_CONTEXT.md`
- `codex/TASK_CONTEXTS/`
- `codex/CONTEXT_ROUTER.md`
- `codex/MODEL_AND_REASONING_POLICY.md`
- `docs/07_implementation/CODEX_QUOTA_EFFICIENCY_PROTOCOL.md`
- quota-efficient start/continue prompts and owner guide.

No real API key, password or production secret should be present.
