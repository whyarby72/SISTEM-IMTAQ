# CHANGELOG

## v1.16 — 2026-09-02 — Codex Quota-Efficient Edition
- Preserved the complete v1.15 architecture/knowledge base while separating it from Codex active working context.
- Added `codex/CURRENT_TASK_CONTEXT.md`, `codex/TASK_CONTEXTS/IMP-S0-001.md`, and `codex/CONTEXT_ROUTER.md`.
- Replaced routine "read everything" startup with minimum active context: `AGENTS.md` + current task context + `NEXT_ACTION.md` + task-specific REQUIRED NOW.
- Compacted root `AGENTS.md`, `START_HERE.md`, `NEXT_ACTION.md`, handoff start and first prompt to reduce repeated context overhead.
- Added `CODEX_QUOTA_EFFICIENCY_PROTOCOL.md`: lazy context expansion, targeted reads/tests, short task threads, meaningful atomic checkpoints, and concise checkpoint output.
- Added model/reasoning policy: Terra default normal coding, Luna routine/light work, Sol hard escalation when selectable; lower reasoning preferred when quality remains sufficient.
- Added owner guide and quota-efficient start/continue prompts.
- No business architecture, task progress, implementation percentage, or current executable task changed. `IMP-S0-001` remains READY at 0%.

# Changelog

## v1.15 — Checkpoint Choice & Session Timebox Control
- Added authoritative `docs/07_implementation/CODEX_CHECKPOINT_TIMEBOX_PROTOCOL.md`.
- DESIGN_LOCKED: after one coherent atomic step, Codex stops at a safe checkpoint instead of silently continuing through the full task.
- Added `SAFE_TO_CLOSE = YES/NO`, exact resume-point reporting and emergency `SAFE CHECKPOINT NOW` behavior.
- Added timed checkpoint choices: Codex offers 2–4 next horizons with estimated ranges, outcomes and confidence; option 1 remains the primary recommendation.
- Estimates are explicitly non-guaranteed planning ranges; Codex must not invent a 5-minute option when no safe short checkpoint exists.
- Added owner guide, safe-checkpoint checklist, checkpoint-choice prompt and emergency-stop prompt.
- Updated AGENTS, Work Protocol, Master/First prompts, handoff navigation and status.
- No application implementation was started; current executable task remains `IMP-S0-001` at 0%.

## v1.14 — Complete Codex Handoff Bundle
- Added `00_CODEX_HANDOFF_START_HERE.md` and `CODEX_FIRST_PROMPT.txt`.
- Added `handoff/` with complete start instructions, reusable prompt library, non-programmer operating guide, safety/acceptance checklists, examples and handoff manifest.
- Preserved the authoritative v1.13 architecture and current executable task `IMP-S0-001`; this version is a handoff/operability packaging upgrade, not a new business-feature implementation.
- Explicitly instructs Codex that the business owner is not responsible for choosing files to edit/upload.

## v1.13 — 2026-09-02
- DESIGN_LOCKED OpenAI API as the selected AI provider for future SISTEM IMTAQ AI implementation.
- Marked self-hosted/local AI, GPU inference and model-serving infrastructure as `OUT_OF_CURRENT_TARGET`; Codex must not implement them without a future approved Decision Gate.
- Retained a thin internal `AIProvider` boundary for testability, fault isolation and prevention of provider-specific code leakage into domain modules; multi-provider routing is not a current requirement.
- Strengthened graceful degradation: missing/invalid API key, rate limit, timeout or OpenAI outage may disable AI features only and must not block Core/domain transactions.
- Reaffirmed backend-only `OPENAI_API_KEY`, feature flags, fake provider for deterministic tests, and Super Admin-only initial AI access.
- Updated AI architecture/provider/roadmap, technical decisions, task queue, project manifest, AGENTS and navigation/status notes.
- AI implementation remains `DEFERRED_FUTURE`; current executable task remains `IMP-S0-001` and implementation/progress percentages are unchanged.

## v1.12 — 2026-09-02
- Added authoritative `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`.
- DESIGN_LOCKED Git/release history as application code version authority; business users are not expected to identify individual source files for upload.
- Added mandatory pre-change impact statement, expected write scope, protected-zone escalation and Minimum Necessary Change rules.
- DESIGN_LOCKED applied-migration immutability; later schema changes use new migrations with compatibility/backup/rollback analysis.
- Added `DEPLOYMENT_STAGING_AND_ROLLBACK.md`: development → staging → production promotion, version-aware releases, persistent-storage/secret separation, smoke tests and rollback/feature-disable/forward-fix strategies.
- Added mandatory `templates/CHANGE_MANIFEST_TEMPLATE.md` for completed non-trivial code changes.
- Refined Sprint 0, Codex work protocol, implementation gates, Definition of Done, task/result templates and risk register to enforce safe maintenance before business implementation.
- Current executable task remains `IMP-S0-001`; implementation/progress percentages are unchanged and coding still requires explicit user authorization.

## v1.11 — 2026-09-02
- Added authoritative `docs/02_architecture/SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`.
- DESIGN_LOCKED teacher and class overlap as backend HARD BLOCKS across recurring schedule rules, generated sessions, extra/ad-hoc sessions, substitution, swap and reschedule/time changes.
- Standardized half-open interval semantics `[start,end)`; exact endpoint adjacency remains allowed until an approved transition-buffer policy says otherwise.
- Required recurrence/effective-date conflict checks to reuse the canonical occurrence resolver rather than weekday/string comparison.
- Added two-stage validation: advisory preflight plus mandatory authoritative recheck inside the persistence transaction.
- Added concurrency protection requirements using transaction-scoped resource serialization/advisory locking or equivalent plus final overlap requery.
- Added PostgreSQL defensive class-session exclusion guidance, DQ conflict backstop, structured conflict errors/audit expectations, SOP/ADR/UAT/task-roadmap integration.
- Added policy-pending items for transition buffer, exclusive locations and any future calendar-aware recurrence exception.
- Carried forward the explicit decision that Google/Gmail SSO is deferred and not a current implementation dependency.
- Current executable task remains `IMP-S0-001`; implementation/progress percentages are unchanged.

## v1.10 — 2026-09-01
- Expanded Shared Communication from parent-report delivery into a broader future institutional Communication Platform.
- Added `ANNOUNCEMENT`, `REMINDER`, and `ACTION_REQUEST` purpose model.
- Added `INSTITUTIONAL_BROADCAST_REMINDER_ACTION_REQUEST.md`, `AUDIENCE_AND_GROUP_REGISTRY.md`, and `TEACHER_AVAILABILITY_CONFIRMATION.md`.
- Added canonical future audiences such as Academic teachers, Wali Kelas, masyayikh, asatidzah, drivers, domain teams and audited managed groups without making provider groups the Source of Truth.
- Locked `Business Event First → Communication Second`: holidays/schedule changes must be canonical before messages are sent.
- Locked teacher readiness semantics: `CONFIRMED ≠ PRESENT`, `NO_RESPONSE ≠ ABSENT`, and `UNAVAILABLE` creates a human-managed Academic exception rather than automatic substitution/reschedule/cancellation.
- Expanded Communication RBAC, cross-module contracts, privacy/audit, provider webhook/response rules and policy-pending register.
- Reworked future communication task queue to include audience resolution, reminders, action requests, teacher Tomorrow Readiness and internal broadcast pilots.
- Communication implementation remains `DEFERRED_FUTURE`; current executable task remains `IMP-S0-001` and implementation progress is unchanged.

## v1.9 — 2026-09-01
- DESIGN_LOCKED Academic Class Master as `Academic Year → Grade Level → Year-Specific Class/Section`.
- Added `docs/02_architecture/CLASS_MASTER_AND_ENROLLMENT.md`.
- Added `grade_levels` contract and expanded `classes` with `grade_level_id` + data-driven `section_code`.
- Explicitly supports 1A/1B, future 1C/2C/etc., variable rombel counts by academic year without code changes.
- Added class-structure ADR/business rules/SOP/UAT coverage.
- Refined `IMP-S2-001`; no new implementation task or progress denominator added.

## v1.8 — 2026-09-01
- Added authoritative `docs/08_ai/AI_RBAC_ACCESS_POLICY.md`.
- Initial AI Assistant rollout is `SUPER_ADMIN` only; all other roles are deny-by-default.
- AI access is permission-based (`ai.assistant.access`, `ai.read`, `ai.draft`, `ai.action`, etc.), not hard-coded to role, so future role grants require no redesign.
- Explicitly preserved the rule that AI never enlarges ordinary business permissions/scopes; technical Super Admin is not an implicit business superuser.
- Added AI-RBAC authorization/evaluation rules and updated future AI tasks for a Super Admin read-only pilot.
- Refreshed RBAC role families to include `ADMIN`, domain Waka roles and executive read-only `KEPALA_UNIT`/`IDAROH`/`YAYASAN` without automatic permission inheritance.
- Current executable task remains `IMP-S0-001`; implementation/progress percentages are unchanged.

## v1.7 — 2026-09-01
- Registered fingerprint/biometric presence only as `FUTURE_CAPABILITY_ONLY`; no current implementation or vendor/device design is authorized.
- Preserved compatibility principles: canonical Student_ID/Staff_ID mapping, biometric scan as supporting evidence only, no-scan ≠ ABSENT, and core attendance remains independent of devices.
- Explicitly prohibited Codex from creating biometric tables, migrations, UI, hardware adapters or implementation tasks until a future institutional Decision Gate approves the feature.
- Current executable task remains `IMP-S0-001`; progress percentages are unchanged.

## v1.5 — 2026-09-01
- Added authoritative `docs/10_shared_core/` specification.
- Formalized canonical Student, Guardian, Staff and Organization boundaries.
- Added Guardian relationship/contact foundation for future Parent Portal/Communication without moving consent into Core.
- Separated Shared Core identity ownership from Shared Platform Auth/RBAC/Audit runtime.
- Added Shared Core service contracts, DQ/privacy rules and 40-case UAT matrix.
- Added `IMP-S1-007` Guardian foundation and `IMP-S1-008` Core contracts/DQ tests.
- Refined `academic_years` as a shared institutional reference while Academic remains owner of semesters/classes/schedules.

## v1.4 — 2026-09-01
- Added a shared **Communication & WhatsApp** future platform architecture; it is not owned by Academic and is not a Source of Truth.
- Added Guardian/Student recipient mapping, contact-channel, consent/preference and personalized-delivery contracts.
- Added provider-neutral `MessagingProvider` design with WhatsApp as the first planned adapter, backend-only secrets, queue/retry/idempotency and verified webhook principles.
- Added parent-report delivery workflow requiring exact published artifact version, recipient eligibility and delivery audit.
- Added versioned message-template, communication privacy/security, delivery observability and phased roadmap documents.
- Added `COMMUNICATION_PLATFORM` change-impact class, communication cross-module contracts and future tasks `FUT-COMM-001`–`FUT-COMM-007`.
- Communication implementation remains deferred and excluded from current Academic/system domain implementation percentages. Current executable task remains `IMP-S0-001`.

## v1.3 — 2026-09-01
- Upgraded the repository from Academic-only framing to the parent **SISTEM IMTAQ multi-module workspace**.
- Added the eight baseline domain module registry and per-module status/AGENTS files.
- Added Shared Core/Platform/Reporting/AI boundaries.
- Added module dependency map, cross-module contract registry and mandatory change-impact classification.
- Added system-level task queue and deterministic next-step resolution so Codex knows what follows the active module.
- Added multi-module progress methodology: Academic implementation + eight-domain system planning/implementation/overall delivery percentages.
- Preserved Academic P1–P13 as the only implementation-ready domain; all other domains remain NOT_PLANNED to prevent Codex from inventing workflows.
- Current executable task remains `IMP-S0-001` and still requires explicit user authorization before coding.

## v1.2 and earlier
See repository history/archive notes from prior workspace versions.

## v1.6 — Parent Portal Architecture
- Added `docs/11_parent_portal/` and `shared/parent_portal/`.
- Parent Portal is a shared Guardian-facing consumer, not a new baseline student-development domain.
- Added Guardian↔User account-link, dynamic child/artifact authorization, secure-link/session, privacy, access-observability, UAT and roadmap contracts.
- Added `PARENT_PORTAL` change-impact class and future Portal contracts/tasks.
- WhatsApp/deep links are explicitly navigation only and never authorization.
- Current implementation task remains `IMP-S0-001`; no code execution started.
