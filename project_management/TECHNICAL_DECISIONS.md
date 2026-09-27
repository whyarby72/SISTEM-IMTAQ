# Technical Decisions

These are implementation-level decisions and must not override business policy.

| ID | Decision | Status |
|---|---|---|
| TECH-001 | Production target uses PostgreSQL. | DESIGN_LOCKED |
| TECH-002 | MVP uses one Laravel modular monolith. | DESIGN_LOCKED |
| TECH-003 | Application code root is `application/web/`. | DESIGN_LOCKED for this workspace |
| TECH-004 | Use UUID technical primary keys where specified; `student_code` is stable business identity. | DESIGN_LOCKED |
| TECH-005 | Actual timestamps use timezone-aware storage; application operational timezone is Asia/Jakarta. | DESIGN_LOCKED |
| TECH-006 | Use Laravel events/jobs; no Kafka/RabbitMQ in MVP. | DESIGN_LOCKED |
| TECH-007 | Exact Laravel version/tooling is selected in Sprint 0 based on the current supported environment and documented here. | DESIGN_ASSUMPTION pending implementation |
| TECH-008 | OpenAI API is the selected AI provider. Keep a thin internal `AIProvider` boundary so OpenAI-specific code does not leak into domain modules; self-hosted/multi-provider infrastructure is not a current target. | DESIGN_LOCKED |
| TECH-009 | Real AI provider API keys are server secrets/environment configuration and never committed or exposed client-side. | DESIGN_LOCKED |
| TECH-010 | End-user AI chat receives allowlisted tools only; no arbitrary SQL/database execution tool. | DESIGN_LOCKED |
| TECH-011 | OpenAI/model availability is optional to core operation; normal transaction workflows must remain operational when AI is disabled/unavailable. | DESIGN_LOCKED |
| TECH-012 | Self-hosted/local AI and GPU/model-serving infrastructure are `OUT_OF_CURRENT_TARGET`; no current sprint may add them without a new approved Decision Gate. | DESIGN_LOCKED |

## TD-MAINT-001 — Controlled Codex maintenance
**Status:** DESIGN_LOCKED  
Git/release history is the Source of Truth for application source versions. Non-trivial changes use impact classification, declared write scope/protected zones, Minimum Necessary Change, tests and a mandatory Change Manifest. Applied migrations are immutable. Production deploy is version-aware and uses a documented staging/rollback path rather than manual file-by-file overwrite. Authority: `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`.
