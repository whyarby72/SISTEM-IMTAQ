# AI Academic Typed Tool Contracts v1.0

Task: AI-A0  
Status: FROZEN_FOR_AI-A1  
Registry size: exactly 5 tools

## Common request constraints

All requests are DTO-validated server-side. IDs must be canonical UUIDs, dates must be valid and ordered, class scope must be authorized, status filters must be allow-listed, and detailed results must be bounded/paginated. Natural-language values never become SQL fragments. No tool accepts a role, SQL, PHP, file path, service name, or arbitrary method name.

## Common response envelope

```json
{
  "tool": "...",
  "status": "OK|NOT_FOUND|AMBIGUOUS|INCOMPLETE|ERROR",
  "data_as_of": "ISO-8601",
  "filters": {},
  "entities": {},
  "metrics": {},
  "records": [],
  "warnings": [],
  "evidence": {
    "source_tool": "...",
    "authorized_user_id": "...",
    "canonical_entity_ids": [],
    "basis_count": 0,
    "semantic_regime": "LEGACY|CANONICAL|MIXED"
  }
}
```

The exact Laravel DTO/result names are implementation choices for AI-A1; the fields and safety meanings are frozen here.

## Tool 1 — `resolve_student`

Input: `query` (bounded string), optional authorized class context.  
Output: `RESOLVED`, `AMBIGUOUS`, or `NOT_FOUND`; on resolution, `student_id`, minimal `display_name`, and class context; on ambiguity, bounded candidates containing only canonical ID, display name, and class context. Never auto-selects the first candidate.

Source: `App\Shared\Core\Models\Student`, with `StudentClassEnrollment` for effective class context. A dedicated resolver is planned; no current AI resolver exists.

## Tool 2 — `get_student_attendance_summary`

Input: canonical `student_id`, `period_start`, `period_end`.  
Output: student identity, period, present/permission/sick/absent counts, resolved and eligible opportunities, missing count, reconciliation count where supported, late count, attendance rate, completeness rate, session/opportunity context, semantic regime, warnings, and `data_as_of`.

Source: `CanonicalAttendanceSemanticService` and `AttendanceSemanticMetricsService`; facts traverse `ClassSession` → `SessionStudentParticipant` → `StudentAttendance`.

## Tool 3 — `get_student_attendance_detail`

Input: canonical `student_id`, period, optional allow-listed status filter, bounded page/limit.  
Output: authorized session/date, subject/class context, attendance outcome, punctuality where available, and authorized reason/notes only. Unrelated internal notes, guardian data, addresses, and health data are excluded.

Source: canonical session participant and attendance relations; semantics delegated to the canonical status mapper/service.

## Tool 4 — `get_class_attendance_summary`

Input: authorized class/group reference, period.  
Output: canonical aggregate metrics, student/class context, eligible opportunity count, outcome counts, attendance/completeness rates, DQ warnings, and semantic regime.

Source: `AttendanceSemanticMetricsService::forClassPeriod`, `AcademicRoleDashboardService`, and `JointAttendanceRosterBreakdownService` for joint class partitioning. A joint session remains one institutional event while class-scoped participant attribution is partitioned by effective enrollment.

## Tool 5 — `get_class_attendance_roster`

Input: authorized class/group reference, period, bounded page/limit.  
Output: factual per-student attendance aggregates and completeness facts. It must not rank, label, diagnose, score discipline, infer character, or recommend punishment.

Source: the same canonical class-period services and effective enrollment partitioning as Tool 4.

## Explicitly excluded tools

`get_students_needing_attention`, risk/prediction/ranking/discipline tools, parent report generation, `write_attendance`, `correct_attendance`, and `create_occurrence` are not in the registry.

## Exact AI-A1 implementation plan (proposed files only)

No file below is created by AI-A0. AI-A1 should use the repository-consistent Academic namespace and these bounded files:

- `application/web/app/Domains/Academic/AI/Contracts/AcademicAiTool.php` — typed tool interface/metadata.
- `application/web/app/Domains/Academic/AI/Contracts/AcademicAiToolRequest.php` — common validated request boundary.
- `application/web/app/Domains/Academic/AI/Contracts/AcademicAiResult.php` — common structured result/evidence envelope.
- `application/web/app/Domains/Academic/AI/Contracts/ResolveStudentRequest.php` and `GetStudentAttendanceSummaryRequest.php` — typed request DTOs.
- `application/web/app/Domains/Academic/AI/Contracts/GetStudentAttendanceDetailRequest.php`, `GetClassAttendanceSummaryRequest.php`, and `GetClassAttendanceRosterRequest.php` — typed bounded read requests.
- `application/web/app/Domains/Academic/AI/StudentIdentityResolver.php` — canonical ID resolution with `RESOLVED`/`AMBIGUOUS`/`NOT_FOUND` outcomes.
- `application/web/app/Domains/Academic/AI/Tools/ResolveStudentTool.php`.
- `application/web/app/Domains/Academic/AI/Tools/GetStudentAttendanceSummaryTool.php`.
- `application/web/app/Domains/Academic/AI/Tools/GetStudentAttendanceDetailTool.php`.
- `application/web/app/Domains/Academic/AI/Tools/GetClassAttendanceSummaryTool.php`.
- `application/web/app/Domains/Academic/AI/Tools/GetClassAttendanceRosterTool.php`.
- `application/web/app/Domains/Academic/AI/AcademicAiToolRegistry.php` — allow-list containing exactly the five tools above.
- `application/web/app/Domains/Academic/AI/AcademicAiAuthorization.php` — server-derived Waka authority boundary; never accepts model-supplied role authority.
- `application/web/tests/Feature/Academic/AI/AcademicAiToolContractTest.php` — envelope, allow-list, validation, and no-write assertions.
- `application/web/tests/Feature/Academic/AI/StudentIdentityResolverTest.php` — resolved, ambiguous, not-found, and class-scope cases.
- `application/web/tests/Feature/Academic/AI/AttendanceReadToolTest.php` — canonical summaries/details/rosters, cutover, joint scope, DQ warnings, and outcome mapping.

AI-A1 must reuse the existing `CanonicalAttendanceSemanticService`, `AttendanceSemanticMetricsService`, `AcademicRoleDashboardService`, `AcademicDashboardExportService`, `AcademicAuthorizationService`, `AuditLogger`, and current model relations. A provider/runtime endpoint is AI-A2 or later, not part of this implementation plan.
