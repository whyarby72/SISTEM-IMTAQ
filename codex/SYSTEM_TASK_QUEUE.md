# SYSTEM-LEVEL TASK QUEUE

This queue tells Codex what comes after the currently active domain queue. It does not authorize implementing an unplanned module.

| System item | Status | Default sequencing | Next gate |
|---|---|---|---|
| Shared Platform/Core + Academic | ACTIVE_READY | current | Shared Core design is in `docs/10_shared_core/`; execute `codex/TASK_QUEUE.md` when user says start |
| Tahfizh | NOT_PLANNED | next recommended domain after Academic pilot unless user reprioritizes | create Tahfizh planning bundle using `templates/MODULE_ONBOARDING_TEMPLATE.md` |
| Kesantrian | NOT_PLANNED | after Tahfizh by default | planning bundle |
| Ruhiyah | NOT_PLANNED | later | planning bundle |
| Kepengasuhan | NOT_PLANNED | later, privacy-sensitive | planning bundle + privacy review |
| Bahasa | NOT_PLANNED | later | planning bundle |
| Kegiatan & Kompetensi | NOT_PLANNED | later | planning bundle |
| Administratif & Layanan | NOT_PLANNED | later | planning bundle |
| Cross-domain Student 360/reporting | FUTURE | after multiple domains are stable | cross-domain contracts/privacy/report design |
| Shared AI | DEFERRED_FUTURE | after trustworthy domain services/semantic layer | follow `docs/08_ai/AI_ROADMAP.md` |
| Parent Portal | DESIGN_READY_FUTURE | after Guardian/User/RBAC/audit + stable published Academic artifact; read-only Academic pilot first | follow `docs/11_parent_portal/PARENT_PORTAL_ROADMAP.md` |
| Shared Communication / WhatsApp | DESIGN_READY_FUTURE | Parent track after Guardian/published-report foundations; internal broadcast/reminder/action-request track after Staff/Organization/RBAC/queue foundations; teacher readiness also needs stable Academic teacher obligations + approved timing/escalation | follow `docs/09_communication/COMMUNICATION_ROADMAP.md` |

## Next-step resolution
1. If `NEXT_ACTION.md` names an executable task, use it.
2. If the active domain queue still has executable dependency-satisfied work, continue that queue.
3. If the active module reaches pilot/accepted completion, consult this file and `project_management/MODULE_ROADMAP.md`.
4. Do not implement a `NOT_PLANNED` module. Start its planning/onboarding gate instead.
5. Any user reprioritization overrides the default sequence after dependency/impact review.
