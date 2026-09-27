# 15 — Codex Build Instructions

## Mission
Implement the Academic module of SISTEM IMTAQ faithfully to this bundle and IMTAQ CORE ENGINE principles. Optimize for data integrity, auditability, simple operations for asatidz/Wali Kelas, and long-term extensibility.

## Hard rules
1. Do not invent business policy.
2. Read `docs/00_governance/POLICY_PENDING_REGISTER.md` before implementing any approval, threshold, lock or officialization path.
3. Never implement anything listed under `SUPERSEDED`.
4. Never make UI behavior the only security control.
5. No destructive deletion of historical business facts.
6. Use explicit domain commands/services, not pure generic CRUD controllers for stateful workflows.
7. Use database constraints in addition to service validation.
8. Make retryable commands idempotent.
9. Use optimistic concurrency (`version_no`) for editable transactional entities.
10. Write automated P0 tests before considering a workflow complete.
11. Reports, exports and dashboards consume canonical facts/semantic services; they do not maintain parallel formulas or values.
12. AI must not be used in MVP transaction authority. AI architecture is documented in `docs/08_ai/` for future implementation and must remain optional.
13. Read `docs/10_shared_core/README.md` before implementing Shared Core identity/Guardian/Staff/Organization/Auth-RBAC-Audit integration.
14. For Academic scheduling writes, read `docs/02_architecture/SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`; no UI-only or bypassable teacher/class conflict control.
15. Before every non-trivial code change, follow `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`: classify impact, declare expected write scope/protected zones, use Minimum Necessary Change, test, and issue a Change Manifest.
16. Never edit an already-applied staging/production migration to rewrite schema history; create a new migration.
17. Do not use manual file-by-file upload or direct production source editing as the canonical deploy/maintenance method.
18. Keep persistent business storage and secrets outside replaceable code releases; never commit production secrets.

## Required domain commands/services
### Core/identity
- CreateStudent
- UpdateStudentIdentity
- AddStudentIdentifier
- CorrectStudentIdentifier
- ApplyStudentStatusChange
- CreateGuardian
- UpdateGuardianIdentity
- LinkGuardianToStudent
- EndGuardianRelationship
- AddGuardianContactChannel
- CorrectGuardianContactChannel
- VerifyGuardianContactChannel
- CreateStaff
- UpdateStaffIdentity
- AssignStaffToOrganizationalUnit
- EndStaffOrganizationalAssignment
- CreateOrganizationalUnit

### Schedule/session
- CheckAcademicScheduleConflicts
- Create/Update/ActivateScheduleRule through conflict-checked service
- GenerateClassSessions
- RequestScheduleChange
- ApproveScheduleChange
- ApplySubstitution
- ApplySwap
- ApplyReschedule
- ApplyCancellation
- CreateExtraSession
- EvaluateAcademicCalendarForSchedule

### Attendance
- SaveStudentAttendanceDraft
- FinalizeStudentAttendance
- CorrectOpenPeriodAttendance
- LockAttendancePeriod
- RequestLockedAttendanceCorrection
- ApproveAttendanceCorrection
- ApplyAttendanceCorrection
- CompleteHistoricalAttendanceHandover

### Grade
- SaveSemesterSubjectGradeDraft / equivalent structure
- officialization/finalize service MUST remain behind POLICY_PENDING workflow contract until policy is supplied
- CorrectSemesterSubjectGrade with audit/version rules

### Report/Transcript
- CheckReportCardReadiness
- GenerateStudentReportCardDraft
- GenerateClassReportCardDrafts
- AddHomeroomReportNote
- ReviewReportCard
- ApproveReportCard
- PublishReportCard
- RenderReportCardPdf
- GenerateCorrectedReportVersion
- GetStudentAcademicHistory
- CheckTranscriptReadiness
- GenerateTranscriptDraft
- ReviewTranscript
- ApproveTranscript
- PublishTranscript
- RenderTranscriptPdf
- GenerateCorrectedTranscriptVersion

### KPI/alert
- CalculateAttendanceMetrics
- CalculateSessionMetrics
- CalculateSemesterGradeMetrics
- GetAttendanceCompleteness
- GetGradeCompleteness
- GetReportCardReadiness
- EvaluateAlertRules

### Migration
- CreateImportBatch
- ProfileImport
- MapImportRows
- ValidateImportBatch
- DryRunImport
- ExecuteImport
- ReconcileImportBatch
- CloseImportBatch

## Transaction boundaries
Commands that update multiple business records must be atomic. Examples:
- Finalize attendance.
- Create/update/activate schedule rule after resource lock + final conflict recheck.
- Apply schedule change only after resulting teacher/class state passes conflict validation.
- Student exit/lifecycle update.
- Post-lock correction apply.
- Import execution unit.

## Event/job guidance
Use Laravel events/jobs; do not introduce Kafka/RabbitMQ in MVP.
Examples:
- AttendanceFinalized → reevaluate DQ/completeness.
- StudentStatusChanged → future-participant eligibility evaluation.
- SemesterGradeChanged → grade/report readiness evaluation.
- ScheduleChangeApplied → session/teacher participation consistency checks.

## UI philosophy
Start from operational decision/task, not database table screens.
Examples:
- Wali Kelas: “today’s class sessions → attendance → finalize → exceptions”.
- Admin: completeness/conflicts/corrections queue.
- Waka: class-level exception/management view.

Avoid exposing internal database state names when a clearer operational label exists, but backend state must remain exact.

## Development stop conditions
Stop and ask for policy rather than guessing when:
- teacher attendance official workflow is required;
- semester-grade finalization/lock authority is required;
- permission must be enforced as mandatory reference;
- report/transcript final approvers/signatories are required;
- Early Warning thresholds/SLA are required;
- any new scoring/ranking/mastery rule is proposed.

## Definition of Done for a feature
A feature is not done until it has:
- business process/use case
- schema/constraints
- authorization/scope
- validation and error handling
- audit/versioning where relevant
- DQ behavior
- automated tests
- UAT traceability
- privacy review
- backup/recovery impact where relevant
- documentation update
- safe-change impact/write-scope evidence
- Change Manifest for non-trivial code work
- staging/deploy/rollback evidence when applicable

## AI future-implementation guardrails
When a future task explicitly authorizes AI implementation:
- use a provider-neutral `AIProvider` boundary with an `OpenAIProvider` adapter;
- keep `OPENAI_API_KEY` server-side only and never commit it;
- prefer current OpenAI Responses/tool-calling capabilities at implementation time rather than scattering provider-specific API code;
- expose only allowlisted domain read/draft/action tools;
- never expose arbitrary SQL/database CRUD;
- reuse normal domain authorization, semantic services and commands;
- require server validation and explicit confirmation for write actions;
- log usage/tool execution without leaking secrets or unnecessary student content;
- keep AI feature-flagged so provider failure does not break normal system operation;
- run AI evaluation/regression cases before production activation.
See `docs/08_ai/`.

## Shared Core authority
System-wide canonical identity/Guardian/Staff/Organization contracts are in `docs/10_shared_core/`. Academic-specific schema documents must not be used to create duplicate or Academic-owned Core masters.


## Session checkpoint control
Read `CODEX_CHECKPOINT_TIMEBOX_PROTOCOL.md` and stop after each coherent atomic step for owner checkpoint selection.
