# AI Write Policy v1.0

**Status:** `DESIGN_LOCKED` safety architecture. Specific domain write tools remain disabled until their underlying workflow is ready.

## 1. Canonical flow

```text
User instruction
   ↓
AI intent/tool proposal
   ↓
Identity/resource resolution
   ↓
RBAC + scope check
   ↓
Structured draft
   ↓
Server business validation
   ↓
Human preview
   ↓
Explicit confirmation
   ↓
Existing domain command
   ↓
Database transaction
   ↓
Audit + AI interaction link
```

AI never writes directly to a table.

## 2. Confirmation
A confirmation must identify the concrete proposed action, not merely reuse an old generic “yes”. For multi-row changes, the user must be able to inspect the affected scope/count and exceptions before confirmation.

If material facts change between preview and execution, confirmation becomes stale and the application must revalidate/re-preview.

## 3. Example: bulk attendance
User may say:

```text
All present except Ahmad sick, Yusuf permission, Umar late.
```

The assistant may prepare individual structured rows. Before commit, the system validates:
- actor is authorized Wali Kelas for the session;
- each student is an expected participant;
- each status is controlled;
- permission reference rules when applicable;
- period/session state permits edit;
- no stale version/concurrency conflict.

Then the human confirms the preview.

## 4. High-risk actions prohibited from autonomous AI execution
AI may explain or prepare information, but cannot autonomously:
- change NIS/NISN;
- alter student lifecycle/status;
- approve post-lock correction;
- approve/publish Report Card or Transcript;
- approve schedule governance changes when human approval is required;
- impose disciplinary sanctions;
- create diagnostic/psychological conclusions;
- score iman, ikhlas, or inner spiritual quality;
- change RBAC/privileged access;
- override audit/history.

## 5. Accountability
The institutional actor remains the authenticated human who confirms and is authorized to execute the command. Audit metadata should additionally mark the channel as `AI_ASSISTED` and link the interaction/tool execution.

## 6. Idempotency
A repeated model/tool request, double confirmation click, timeout retry, or provider retry must not create duplicate transactions. Domain idempotency remains mandatory.

## 7. No silent fallback
If the AI cannot confidently resolve student/session/period or receives a validation error, it must surface the ambiguity/error. It must not guess missing identifiers, statuses, dates, or business policy.

## 8. AI capability and business authority remain separate
Before any DRAFT/ACTION tool is exposed, the server must verify both the AI capability permission and the underlying domain permission/scope/state. `SUPER_ADMIN` has full institution-wide authority, but AI still cannot perform a business write outside the normal application workflow or skip required human approval, audit or versioning.
