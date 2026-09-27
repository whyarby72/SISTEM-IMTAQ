# AI Evaluation Plan v1.0

**Status:** `DESIGN_LOCKED` evaluation requirement; test cases are created when AI implementation begins.

## 1. Evaluation before production
No AI capability may be enabled in production merely because a demo appears useful. Each activated tool/capability requires representative evaluations.

## 2. Evaluation dimensions

### Factual grounding
- answer uses returned canonical facts;
- no invented values;
- missing values remain missing;
- dates/statuses are not silently inferred.

### Authorization
- unauthorized user cannot retrieve restricted data through chat;
- cross-class/cross-domain scope is enforced server-side;
- denied tool calls do not leak data in error messages.

### Tool selection
- correct tool is selected for intent;
- no tool call when answer can be safely given without one;
- no write tool for a read-only request.

### Structured write accuracy
- correct student/session/subject/semester resolution;
- controlled vocabulary only;
- ambiguous names trigger clarification/candidate resolution;
- invalid score/status is rejected.

### Human control
- write preview is understandable;
- stale preview is detected;
- no commit without confirmation;
- high-risk action remains non-autonomous.

### Privacy
- unnecessary identifiers/notes are not included in provider context;
- parent/internal-role boundaries are maintained.

### Reliability
- provider timeout/failure leaves business data unchanged;
- retries do not duplicate domain transactions.

## 3. Golden cases
Maintain versioned test cases for common IMTAQ questions/actions, including:
- attendance summary;
- incomplete attendance;
- report readiness explanation;
- ambiguous student name;
- unauthorized class request;
- missing grade;
- cancelled/rescheduled session;
- bulk attendance draft;
- provider failure;
- prompt injection attempt embedded in retrieved content.

## 4. Automated vs human evaluation
Automate deterministic checks such as tool authorization, schemas, expected IDs/values, no-write-without-confirmation and idempotency. Human review remains required for response usefulness/tone and sensitive reporting quality.

## 5. Regression
Prompt, model, tool schema, provider adapter or domain-contract changes that can alter AI behavior require regression evaluation before production rollout.

## 6. AI-RBAC authorization cases
Before production rollout, include at minimum:
- non-Super-Admin role with no AI grant cannot open/use the assistant;
- Super Admin with AI shell/read permission but without underlying domain read permission cannot retrieve that domain's restricted data;
- granting `ai.assistant.access` + `ai.read` to a future role exposes only tools permitted by that role's normal domain scope;
- `ai.draft`/`ai.action` remain unavailable while global capability flags are disabled;
- executive read-only user with future AI READ access cannot invoke business write tools;
- AI permission grant/revoke takes effect and is auditable.
