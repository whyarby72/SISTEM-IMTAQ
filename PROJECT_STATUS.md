# PROJECT STATUS — SISTEM IMTAQ v1.16

**Workspace:** multi-module parent repository  
**Current delivery strategy:** Academic-first modular monolith  
**Implementation state:** `PILOT_IMPLEMENTED_HARDENING`
**Current canonical gate:** `SOC-MD-06` (management decision; source mutation not authorized)  
**Application root:** `application/web/`

## Domain readiness
- Academic: `IMPLEMENTED / PILOT / HARDENING`; implementation is substantially complete, with canonicalization and policy gates still open.
- Tahfizh: `NOT_PLANNED`.
- Kesantrian / Adab & Kedisiplinan: `NOT_PLANNED`.
- Ruhiyah / Ibadah Terobservasi: `NOT_PLANNED`.
- Kepengasuhan: `NOT_PLANNED`.
- Bahasa: `NOT_PLANNED`.
- Kegiatan & Kompetensi: `NOT_PLANNED`.
- Administratif & Layanan: `NOT_PLANNED`.

Canonical registry: `modules/MODULE_REGISTRY.json`.

## Shared readiness
- Shared Core: implemented foundation evidenced by current source/tests; future cross-domain expansion remains separately governed.
- Shared Platform: pre-implementation.
- Cross-domain Student 360/reporting: future.
- Shared AI architecture: design ready future; implementation deferred. Initial access policy is `SUPER_ADMIN` only for AI Assistant/READ, permission-based and deny-by-default for every other role; underlying domain RBAC still applies.
- Shared Communication/WhatsApp architecture: `DESIGN_READY_FUTURE` v1.1; now covers Parent delivery plus institutional `ANNOUNCEMENT`, `REMINDER`, and `ACTION_REQUEST`, dynamic Staff audiences/managed groups, and future teacher availability confirmation. Implementation remains deferred until identity/audience, RBAC/audit/queue/privacy and provider gates are ready.
- Parent Portal architecture: `DESIGN_READY_FUTURE` v1.0 — read-only first, Guardian-linked authenticated access to exact published parent-approved artifacts; implementation deferred.

## Academic planning history
P1 PostgreSQL Schema ✅  
P2 RBAC ✅  
P3 Workflow/Lock/Correction ✅  
P4 Operational SOP ✅  
P5 Semester Grade ✅  
P6 Report Card ✅  
P7 Transcript ✅  
P8 KPI/Reporting ✅  
P9 Data Quality/Early Warning ✅  
P10 Historical Migration ✅  
P11 UAT ✅  
P12 Codex Handoff ✅  
P13 Sprint Roadmap ✅  
Architecture Consistency R1 ✅
Schedule Conflict & Constraint Engine v1.0 ✅

## Policy pending
Academic still has policy-dependent paths (teacher attendance final workflow, grade officialization/lock, report/transcript final authority, student early-warning thresholds, permission details). These paths must remain disabled/structural until approved. See `docs/00_governance/POLICY_PENDING_REGISTER.md`.

## Change governance / safe maintenance
Codex change management is now `DESIGN_LOCKED` in `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`. Git/release history is the code-version authority; non-trivial changes require impact classification, expected write scope/protected-zone control, Minimum Necessary Change, automated regression, Change Manifest and an explicit deploy/rollback path. Applied migrations are immutable. Business users are not expected to choose individual files to upload. Shared Core, public contracts, cross-domain, global database/security, AI/Communication/Portal changes require broader impact review per `modules/CHANGE_IMPACT_RULES.md`.


## Future capability note — biometric/fingerprint
Fingerprint/biometric presence is registered only as `FUTURE_CAPABILITY_ONLY`. It has no current implementation task, no MVP dependency and must not change the active Academic attendance workflow. Detailed design begins only if a future institutional Decision Gate approves the need.

## Authentication provider note
Google/Gmail SSO is deferred and is not a current implementation dependency. Preserve internal User/Staff/Guardian identity separation and RBAC; do not add Google OAuth work unless explicitly reactivated.

## RBAC / AI access note
Current organizational model recognizes technical `SUPER_ADMIN`, operational `ADMIN`, domain Waka (`WAKA_AKADEMIK`, `WAKA_TAHFIZH`, `WAKA_KESANTRIAN`), domain members, and executive read-only (`KEPALA_UNIT`, `IDAROH`, `YAYASAN`). Organizational hierarchy does not automatically inherit write permissions. AI access initially belongs only to `SUPER_ADMIN`; future role access is an audited RBAC grant.

## Latest design refinements
- Codex Change Management & Safe Maintenance v1.0 is DESIGN_LOCKED: Git versioning, branch isolation, protected zones, Minimum Necessary Change, migration immutability, Change Manifest, staging promotion, version-aware deployment and rollback are mandatory maintenance guardrails.
- Academic Schedule Conflict & Constraint Engine v1.0 is DESIGN_LOCKED: teacher/class overlap is a backend hard block across recurring rules, actual sessions, extra sessions and schedule changes; final validation is transactional and concurrency-protected.
- Academic Class Master remains DESIGN_LOCKED as `Academic Year → Grade Level → Class/Section`; section codes are configurable data.
- Shared Communication v1.1 now supports future internal broadcasts, scheduled reminders and structured action requests in addition to Parent delivery.
- Teacher availability confirmation is readiness context only: `CONFIRMED ≠ PRESENT`, `NO_RESPONSE ≠ ABSENT`, and `UNAVAILABLE` never auto-mutates the Academic schedule.
- Future communication implementation tasks are explicitly deferred; current canonical activity is session-occurrence governance, not a new implementation sprint.

## AI provider decision — carried forward into v1.14
- `DESIGN_LOCKED`: OpenAI API is the selected AI provider for future IMTAQ AI implementation.
- Self-hosted/local AI is `OUT_OF_CURRENT_TARGET`; no GPU/model-server dependency is planned.
- A thin `AIProvider` boundary remains for isolation/testability, not to require multi-provider architecture.
- OpenAI API availability/key is never a dependency for normal Core/domain operation; AI must fail gracefully.
- AI implementation remains `DEFERRED_FUTURE`; initial access policy remains Super Admin only.


## Codex quota-efficiency control — v1.16
`DESIGN_LOCKED`: the full bundle remains the knowledge base, but active Codex context uses `AGENTS.md → codex/CURRENT_TASK_CONTEXT.md → NEXT_ACTION.md → REQUIRED NOW`, with lazy expansion through `codex/CONTEXT_ROUTER.md`. Normal coding prefers GPT-5.6 Terra when selectable, Luna for routine/light work, and Sol only for hard escalation. This changes no business architecture or implementation progress.

## Codex checkpoint/session control — v1.16
`DESIGN_LOCKED`: active Codex implementation/maintenance sessions use `docs/07_implementation/CODEX_CHECKPOINT_TIMEBOX_PROTOCOL.md`. After one coherent atomic step, Codex must checkpoint, report `SAFE_TO_CLOSE = YES/NO`, persist an exact resume point, and present 2–4 next checkpoint choices with estimated time ranges and confidence. The owner selects the next horizon; Codex must not silently continue beyond it. Estimates are ranges, not completion guarantees.
