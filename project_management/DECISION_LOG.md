# Decision Log

Use this log for decisions made after this workspace version. Do not duplicate all ADRs here; authoritative historical decisions are in `docs/02_architecture/ARCHITECTURE_DECISIONS.md`.

| Date | ID | Decision | Status | Affected files |
|---|---|---|---|---|
| 2026-09-01 | WS-001 | Use this repository structure as the Codex project workspace; application code will live under `application/web/`. | DESIGN_LOCKED | README, AGENTS, roadmap |
| 2026-09-01 | WS-002 | `NEXT_ACTION.md` + `codex/TASK_QUEUE.md` are the persistent next-step mechanism. Codex updates them after every completed task. | DESIGN_LOCKED | AGENTS, WORK_PROTOCOL |
| 2026-09-01 | CORE-WS-001 | Shared Core v1.0 owns canonical Student, Guardian, Staff, Organization identity/relationships; Shared Platform owns auth/RBAC/audit runtime; domains own transactions. | DESIGN_LOCKED | docs/10_shared_core, shared/core, module contracts |
| 2026-09-01 | AI-RBAC-001 | Initial IMTAQ AI Assistant access is `SUPER_ADMIN` only; access is implemented through RBAC permissions, all other roles deny-by-default, and AI never expands underlying business authorization. | DESIGN_LOCKED | docs/08_ai/AI_RBAC_ACCESS_POLICY.md, RBAC_MATRIX.md, AGENTS.md |
| 2026-09-02 | ACA-SCHED-001 | Teacher/class schedule overlap is a backend HARD BLOCK with canonical recurrence/effective-date evaluation, transactional recheck and concurrency protection; no routine ignore-conflict bypass. | DESIGN_LOCKED | SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md, Business Rules, PostgreSQL, UAT, Sprint 3/4 |
| 2026-09-02 | AUTH-WS-001 | Google/Gmail SSO is deferred and is not a current implementation dependency; external login provider must not become canonical Staff/Guardian identity. | DEFERRED_FUTURE | MVP_FUTURE_SUPERSEDED.md, PROJECT_STATUS.md, PROJECT_MANIFEST.json |
| 2026-09-02 | MAINT-WS-001 | Codex maintenance uses Git/release history, impact classification, protected zones, Minimum Necessary Change, immutable applied migrations, Change Manifest, staging promotion and rollback planning; the business user is not responsible for choosing individual deploy files. | DESIGN_LOCKED | docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md, DEPLOYMENT_STAGING_AND_ROLLBACK.md, AGENTS.md, WORK_PROTOCOL.md |
