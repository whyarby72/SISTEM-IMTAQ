# AI Architecture Layer v1.1

**Status:** `DESIGN_LOCKED` architecture; implementation is `DEFERRED_FUTURE` until transaction foundations are stable.

## 1. Purpose
Provide one AI-native conversational interface for SISTEM IMTAQ that can eventually:
- answer authorized questions from canonical/semantic data;
- summarize validated facts;
- prepare structured draft transactions;
- execute narrowly controlled domain commands only after authorization, validation and explicit human confirmation.

AI is not required for Academic MVP operation. If the AI provider is unavailable, Core/Academic/Tahfizh/Kesantrian transactions must continue normally.

## 2. Architecture

```text
Authenticated User
       │
       ▼
IMTAQ AI Chat UI
       │
       ▼
AI Orchestrator
       │
       ├── Authorization / Scope Resolver
       ├── Tool Registry / Policy Engine
       ├── Context Minimizer
       ├── Provider Adapter
       └── AI Interaction / Usage Audit
       │
       ├──────── READ TOOLS ────────► Domain Query/Semantic Services
       │
       └──── DRAFT/ACTION TOOLS ───► Domain Commands
                                      │
                                      ▼
                               Canonical PostgreSQL
```

The model never receives direct database credentials and never executes arbitrary SQL.

## 3. Selected provider and internal boundary
**Selected production AI provider: OpenAI API.** Self-hosted/local model infrastructure is outside the current target and must not be implemented unless a future management decision reopens it.

Application/domain code still depends on a thin internal contract:

```text
AIProvider
  └── OpenAIProvider   -- selected production implementation
```

This boundary is retained for testability, failure isolation and to prevent OpenAI-specific calls from spreading through Academic/Tahfizh/Kesantrian modules. It is **not** a requirement to build multi-provider routing or self-hosted AI in the current roadmap.

Provider responsibilities:
- send model input/tool definitions;
- receive model output/tool calls;
- expose token/usage metadata when available;
- normalize provider errors;
- never make business authorization decisions.

Orchestrator/domain responsibilities:
- determine which tools are available to the authenticated user;
- minimize data sent to the provider;
- execute server-side validation;
- require confirmation for write actions;
- keep audit/usage records.

## 4. Initial access rollout
Authoritative role/capability rules are in `AI_RBAC_ACCESS_POLICY.md`.

Initial production rollout is **Super Admin only** at the AI-shell level:
- `SUPER_ADMIN`: AI Assistant access + READ capability may be granted by default;
- all other roles: deny-by-default until explicitly granted through RBAC;
- DRAFT/ACTION capabilities remain globally disabled until later AI gates.

Super Admin's full institution-wide authority does not change AI governance. Every tool still requires the underlying domain permission, effective scope and valid resource state; AI cannot bypass normal business authorization or workflow.

AI access is permission-based so it can later be granted/revoked for another role without redesigning the application.

## 5. Capability levels

### Level A — AI READ ASSISTANT
Allowed:
- search authorized entities;
- query summaries/KPI through canonical semantic services;
- compare and explain validated facts;
- explain readiness/DQ reasons.

Forbidden:
- create/update/delete/approve/publish official data.

Recommended first implementation stage.

### Level B — AI DRAFT ASSISTANT
Allowed:
- convert user language into a structured proposal;
- prepare attendance/grade/note drafts where the underlying domain policy permits;
- show preview and validation errors.

The draft is not an official transaction merely because the model produced it.

### Level C — AI CONTROLLED ACTION ASSISTANT
Allowed only for explicitly whitelisted commands after:
1. authenticated actor authorization;
2. structured arguments;
3. server-side validation;
4. explicit human confirmation;
5. command execution through the same domain service used by normal UI;
6. audit recording.

High-risk approval/publication/lifecycle decisions remain human-controlled.

## 6. Non-negotiable AI invariants

- `AI-001` AI never becomes Source of Truth.
- `AI-002` AI can read only data the authenticated user is authorized to read.
- `AI-003` AI writes only through explicit domain commands.
- `AI-004` No arbitrary SQL/database execution tool in production.
- `AI-005` AI-assisted writes use structured, schema-validated arguments.
- `AI-006` Server business validation is mandatory even when model output is structurally valid.
- `AI-007` AI write actions require explicit human confirmation unless a later approved policy defines a lower-risk exception.
- `AI-008` AI-assisted actions are auditable and identify the human actor.
- `AI-009` AI cannot approve its own output.
- `AI-010` AI cannot autonomously publish sensitive reports, alter student lifecycle, alter identifiers, impose discipline, or create spiritual/faith scores.
- `AI-011` AI failure must not block normal system operation.
- `AI-012` Minimum necessary context only; do not send entire database/table dumps to the model.
- `AI-013` Internal cross-domain notes remain domain-restricted even when one Student_ID links the domains.
- `AI-014` OpenAI API is the selected AI provider; provider/model configuration remains outside domain business policy.

## 7. No direct natural-language-to-SQL production path
The production chatbot must not expose an `execute_sql(sql)` tool. Query capability is implemented through explicit read services/tools such as:
- `find_student`
- `get_class_attendance_summary`
- `get_student_academic_history`
- `get_report_card_readiness`

This preserves RBAC, KPI semantics, auditability, and schema encapsulation.

## 8. Cross-domain future compatibility
One assistant may expose tools from multiple modules, but each tool keeps its own ownership and scope:

```text
IMTAQ AI
 ├── Core tools
 ├── Academic tools
 ├── Tahfizh tools      (future)
 ├── Kesantrian tools   (future)
 └── Other domain tools (future)
```

Student 360/cross-domain summaries are derived consumers. AI does not merge domain transactions into a new source-of-truth record.

## 9. Availability behavior
If AI is disabled, key is missing, provider is rate-limited, or provider fails:
- AI UI reports temporary unavailability;
- no partial official transaction is committed;
- standard UI/API workflows continue;
- the failure is logged without exposing secrets.

### Additional RBAC invariants
- `AI-015` Initial AI Assistant rollout is restricted to `SUPER_ADMIN`; all other roles are deny-by-default.
- `AI-016` AI access is controlled by RBAC permission/capability, not a hard-coded role check.
- `AI-017` AI permission never expands underlying domain permissions or data scope.
- `AI-018` Future role enablement must be auditable and regression-tested before production use.
- `AI-019` Core SISTEM IMTAQ operation must never require a successful OpenAI request.
- `AI-020` Self-hosted/local AI is not a current implementation target and must not add GPU/model-server dependencies to Sprint 0–12.
- `AI-021` OpenAI-specific SDK/API code is confined to the AI integration boundary; domain modules consume internal AI services/tools only.
