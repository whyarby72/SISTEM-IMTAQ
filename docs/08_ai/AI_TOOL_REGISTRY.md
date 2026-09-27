# AI Tool Registry v1.0

**Status:** `DESIGN_LOCKED` registry pattern; individual tools are activated only when their domain dependencies and policies are ready.

## 1. Tool contract
Every AI tool must define:
- `tool_code`
- module/domain owner
- capability class: `READ`, `DRAFT`, `ACTION`
- required permission(s)
- scope resolver
- input JSON schema
- output schema
- canonical service/command invoked
- allowed workflow/resource states
- confirmation requirement
- audit requirement
- sensitivity classification
- rate/usage class
- test cases

No tool is available merely because a backend method exists.

## 2. Initial Academic read-tool candidates

| Tool | Class | Purpose | Source |
|---|---|---|---|
| `find_student` | READ | Resolve an authorized student candidate | Core student service |
| `get_student_academic_summary` | READ | Validated Academic facts for one student | Academic semantic services |
| `get_class_schedule` | READ | Authorized schedule/session view | Academic schedule service |
| `get_class_attendance_summary` | READ | Status counts/completeness | P8 semantic metrics |
| `get_attendance_exceptions` | READ | Incomplete/missing attendance for actor scope | DQ/attendance services |
| `get_semester_grades` | READ | Authorized semester-grade facts | Grade service |
| `get_report_card_readiness` | READ | Explain readiness blockers | Report readiness service |
| `get_student_academic_history` | READ | Longitudinal official grade history | Academic history view |
| `get_active_academic_alerts` | READ | Authorized internal exception queue | Alert service |

Tools must return structured facts, not prewritten conclusions when the conclusion can be derived by the model.

## 3. Draft/action candidates
These remain disabled until their underlying workflow is production-ready.

| Tool | Class | Dependency |
|---|---|---|
| `prepare_student_attendance_draft` | DRAFT | Student Attendance Sprint 5 |
| `save_student_attendance_draft` | ACTION | Attendance command + confirmation |
| `prepare_semester_grade_draft` | DRAFT | Grade structure + approved actor policy |
| `save_semester_grade_draft` | ACTION | Grade workflow authorization |
| `prepare_homeroom_report_note` | DRAFT | Report note workflow |

## 4. Permanently prohibited generic tools
Do not implement:
- `execute_sql`
- `run_query` accepting arbitrary SQL
- `update_database_record(table, fields)`
- unrestricted generic CRUD tool
- arbitrary shell/code execution from the end-user AI chat
- tools that bypass domain authorization or workflow state checks.

## 5. Tool availability resolution
Available tools for each AI request are computed from:

```text
Authenticated user
 + `ai.assistant.access`
 + AI capability permission (`ai.read` / `ai.draft` / `ai.action`)
 + underlying domain permission
 + effective data scope
 + module/tool feature flag
 + resource/workflow state
 = allowed tool set
```

Do not give the model a tool and rely on it to decide whether the user should have access.

## 6. Student ambiguity
A name match is not enough for a write tool. If a request says “Ahmad”, the system must resolve ambiguity through controlled candidate selection before any transaction is proposed or committed.

## 7. Initial rollout
At first enablement, expose the AI Assistant/tool registry only to `SUPER_ADMIN`, and only READ tools whose underlying domain permissions/scopes are also satisfied. Other roles receive no AI tools until explicitly granted through RBAC. DRAFT/ACTION tools remain feature-disabled until later AI gates.
