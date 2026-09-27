# AI RBAC Access Policy v1.0

**Status:** `DESIGN_LOCKED`.

## 1. Initial rollout rule
At first production enablement, the IMTAQ AI Assistant is available only to the `SUPER_ADMIN` role.

Default role grants:

| Role | AI Assistant | AI READ | AI DRAFT | AI ACTION |
|---|---:|---:|---:|---:|
| `SUPER_ADMIN` | ALLOW | ALLOW | DENY by default | DENY by default |
| `ADMIN` | DENY | DENY | DENY | DENY |
| `WAKA_AKADEMIK` | DENY | DENY | DENY | DENY |
| `WAKA_TAHFIZH` | DENY | DENY | DENY | DENY |
| `WAKA_KESANTRIAN` | DENY | DENY | DENY | DENY |
| Domain members | DENY | DENY | DENY | DENY |
| `KEPALA_UNIT` | DENY | DENY | DENY | DENY |
| `IDAROH` | DENY | DENY | DENY | DENY |
| `YAYASAN` | DENY | DENY | DENY | DENY |
| Guardian/Parent | DENY | DENY | DENY | DENY |

`AI DRAFT` and `AI ACTION` remain globally feature-gated until their roadmap gates are satisfied even for `SUPER_ADMIN`.

## 2. Permission-based, not hard-coded
Do **not** implement AI visibility as `if role == SUPER_ADMIN`. Use normal RBAC permissions so access can later be granted/revoked without redesign.

Minimum permission catalog:
- `ai.assistant.access` — may open/use the AI Assistant shell.
- `ai.read` — may invoke AI READ tools, still subject to each tool's underlying business permission and scope.
- `ai.draft` — may prepare structured drafts where the domain workflow allows it.
- `ai.action` — may request explicitly allowlisted controlled actions; never bypasses domain authorization.
- `ai.platform.manage` — AI provider/model/tool/feature configuration; technical administration only.
- `ai.usage.view` — view AI usage/cost observability according to scope.
- `ai.audit.view` — view AI interaction/tool audit according to scope.

A future implementation may add narrower tool-specific permissions, but these baseline permissions must remain the capability boundary.

## 3. Effective AI authorization
AI never enlarges the user's ordinary application privilege.

```text
EFFECTIVE AI TOOL ACCESS
=
AI ASSISTANT/CAPABILITY PERMISSION
∩ UNDERLYING DOMAIN PERMISSION
∩ DATA SCOPE
∩ RESOURCE STATE
∩ TOOL FEATURE FLAG
```

Examples:
- A `SUPER_ADMIN` who has `ai.assistant.access` and `ai.read` but no authorized Academic read scope does **not** gain Academic student-data access merely through AI.
- If the same person is also explicitly assigned an authorized cross-domain read role/scope, AI may use only the corresponding read tools/data.
- Enabling `ai.assistant.access` for a future `WAKA_AKADEMIK` does not expose Tahfizh/Kesantrian data because normal domain permissions and scopes still apply.

## 4. Super Admin authority does not bypass AI governance
`SUPER_ADMIN` is the initial AI user because AI is being piloted under controlled governance. Super Admin has full institution-wide authority across enabled domains, but AI access remains a separate capability and never bypasses business permission, scope, resource state, workflow or human confirmation.

Therefore:
- AI shell access does not imply unrestricted database/report access.
- AI read tools still require ordinary read permission/scope.
- AI draft/action tools still require the exact business permission/state required by the normal UI/API workflow.
- Super Admin may hold additional approved operational/read roles if the institution intentionally grants them; these are explicit assignments, not implicit inheritance.

## 5. Future grant to other roles
A role may receive AI access later without code changes by granting one or more AI permissions through RBAC.

Required process for a new role grant:
1. identify role and business purpose;
2. identify allowed capability (`READ`, `DRAFT`, `ACTION`);
3. confirm underlying domain permissions/scopes;
4. confirm tool allowlist and privacy classification;
5. update RBAC configuration/assignment;
6. audit the grant/revoke;
7. run authorization/privacy regression tests before production enablement.

All other roles are deny-by-default until explicitly granted.

## 6. Executive read-only roles
If AI is later enabled for `KEPALA_UNIT`, `IDAROH`, or `YAYASAN`, their executive read-only model remains intact:
- AI may summarize only reports/data they are authorized to preview/view;
- AI cannot create/update/finalize/approve/lock/publish business transactions merely because AI is enabled;
- sensitive internal notes remain excluded unless separately authorized by policy.

## 7. Audit requirements
AI access changes are high-value security events. Record:
- affected role/user;
- permission granted/revoked;
- actor;
- timestamp;
- reason/reference;
- previous/new effective permission where practical.

AI-assisted business actions also record the authenticated human actor, AI interaction/tool execution, confirmation, and the underlying domain command result.

## 8. Feature flags
Recommended capability gates:

```text
IMTAQ_AI_ENABLED
IMTAQ_AI_READ_ENABLED
IMTAQ_AI_DRAFT_ENABLED
IMTAQ_AI_WRITE_ENABLED
```

RBAC permission and feature flag are both required. A role grant must not activate a globally disabled capability.

## 9. Locked invariants
- `AI-RBAC-001` AI Assistant access is permission-based, not role-hard-coded.
- `AI-RBAC-002` Initial AI Assistant access is granted only to `SUPER_ADMIN`.
- `AI-RBAC-003` All other roles are deny-by-default.
- `AI-RBAC-004` AI capabilities are separated into READ, DRAFT and ACTION.
- `AI-RBAC-005` AI never expands the user's underlying business permissions.
- `AI-RBAC-006` Every AI tool remains subject to domain permission, data scope, resource state and server-side validation.
- `AI-RBAC-007` AI access can later be granted/revoked through RBAC without redesigning the application.
- `AI-RBAC-008` Executive read-only roles remain read-only even if AI access is later enabled.
- `AI-RBAC-009` AI ACTION requires explicit feature enablement, tool allowlisting, audit and the normal domain command authorization.
- `AI-RBAC-010` Grant/revoke of AI privilege is audited.
