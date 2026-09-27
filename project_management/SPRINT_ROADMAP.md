# P13 — Sprint & Implementation Roadmap v1.0

This roadmap is the canonical implementation sequence. Codex must use `codex/TASK_QUEUE.md` for task-level execution and this document for phase intent/dependencies.

## Sprint 0 — Repository & Development Foundation
**Goal:** reproducible Laravel/PostgreSQL workspace; no business feature yet.
- Initialize Laravel under `application/web/`.
- Configure environment, timezone, database and test harness.
- Establish Git/branch/change-ID conventions, code-quality/test commands and protected-zone/change-impact discipline.
- Establish migration immutability, Change Manifest, secret/persistent-storage separation and repeatable deployment/release metadata conventions.
- Document development → staging → production promotion and rollback/feature-disable target.
- Add project bootstrap instructions.

**Exit:** Gate A.

## Sprint 1 — Core Identity, Organization, RBAC & Audit
**Goal:** One Student ID and secure shared foundation.
- Users/staff/organizational units/locations and effective Staff organization context.
- Students, identifiers, status history.
- Canonical Guardian master, Student↔Guardian relationships and contact-channel baseline.
- Effective-dated role assignments and backend authorization scopes.
- Append-only audit infrastructure and correction-request foundation.
- Shared Core service contracts + Core DQ/privacy/security contract tests.

**Exit:** Core P0 identity/RBAC tests pass.

## Sprint 2 — Academic Structure, Enrollment, Homeroom & Calendar
**Goal:** establish who belongs where and when.
- Shared institutional academic-year reference + Academic semesters/grade levels/year-specific classes/rombel/subjects; section codes are data-driven (A/B/C/...).
- Student class enrollment.
- Effective-dated homeroom assignment.
- Academic calendar events and generation policies.

**Exit:** enrollment/homeroom/calendar integrity tests pass.

## Sprint 3 — Teaching Assignment, Schedule & Session Engine
**Goal:** flexible class-specific schedule → actual session backbone.
- Teaching assignments.
- Recurring schedule rules and week-of-month support.
- Schedule Conflict & Constraint Engine: teacher/class hard blocks, recurrence/effective-date evaluation, preflight + authoritative transactional recheck, concurrency protection, DQ backstop.
- Idempotent session generation.
- Expected roster snapshot.
- Extra/ad-hoc session support.

**Exit:** schedule/session P0/P1 tests pass.

## Sprint 4 — Schedule Changes & Teacher Participation Structure
**Goal:** preserve obligation lineage without false absence.
- Substitution, swap, reschedule, cancellation with atomic resulting-state conflict validation.
- Teacher participation structure and obligation types.
- Teacher attendance official workflow remains disabled until policy is approved.

**Exit:** schedule-change lineage tests pass.

## Sprint 5 — Student Attendance MVP
**Goal:** operational Wali Kelas workflow.
- Wali Kelas scoped attendance entry.
- Save DRAFT and atomic Finalize → VALIDATED.
- Session completion side effect.
- Permission reference validation when present.
- Paper remains backup only.
- Mobile/responsive operational UI.

**Exit:** attendance P0/P1 tests pass.

## Sprint 6 — Attendance Governance, Lock, Correction & DQ
**Goal:** trustworthy official attendance lifecycle.
- Open-period correction + reason/audit.
- Class/date-range period locks.
- Post-lock correction request/review/apply.
- Historical homeroom handover command.
- Deterministic attendance Data Quality rules and Admin exception view.

**Exit:** Gate D / pilot-ready attendance.

## Sprint 7 — Semester Grade Structure
**Goal:** one final semester grade per Student×Subject×Semester without inventing assessment components.
- `semester_subject_grades` structure, range/uniqueness/audit.
- Direct-entry/import provenance.
- Grade completeness queries.
- Officialization/final lock path remains feature-flagged until policy is approved.

**Exit:** structural grade tests pass; official publication remains gated if policy unresolved.

## Sprint 8 — Academic Report Card & Transcript
**Goal:** immutable versioned publication artifacts consuming canonical grades/attendance.
- Report-card readiness and structured snapshots.
- Homeroom report note.
- Report versioning/reissue.
- Academic history view.
- Transcript snapshots/versioning.
- PDF rendering/storage after approval path is available.

**Dependency:** official publication actions require relevant grade/report approval policies.

## Sprint 9 — KPI / Semantic Reporting
**Goal:** one metric, one definition.
- Attendance/session/grade completeness metrics.
- Student/class/admin/Waka views by role.
- Correct opportunity-based aggregation.
- Export through the same semantic services.
- No generic Attendance % until approved definition exists.

## Sprint 10 — Shared Alert Infrastructure & Academic DQ
**Goal:** actionable exceptions with owner/status/resolution.
- Alert rules, alerts, status history, actions, deduplication.
- Activate deterministic Academic DQ/operational rules.
- Student Early Warning thresholds remain inactive until management approval.

## Sprint 11 — Historical Migration Tooling
**Goal:** safe, auditable legacy import.
- Import batches/files/rows/errors/mappings/lineage.
- Dry-run and reconciliation.
- Legacy daily/monthly layers only if source inventory proves needed.
- First controlled import batches.

## Sprint 12 — Hardening, UAT, Pilot & Cutover
**Goal:** production acceptance.
- Full negative RBAC/security testing.
- Concurrency/idempotency/atomicity tests.
- Performance baseline.
- Backup + restore test.
- UAT evidence and defect closure.
- 1–2 class controlled pilot.
- Migration/cutover decision and operational training.

**Exit:** Gate G.

## Explicitly not a sprint dependency
Future detailed assessments, remedial engine, student ranking/composite score, AI reporting, profile photos, cross-domain parent development report and microservices are not MVP blockers.

## Future Phase — Shared AI-native Layer (not an MVP dependency)
This phase begins only after relevant transaction/RBAC/audit/semantic services are stable and the user explicitly authorizes AI implementation.
1. Thin AIProvider boundary + selected OpenAI API adapter + server-side secret/feature-flag configuration.
2. Read-only AI assistant with allowlisted Academic tools and evaluation suite.
3. Draft assistant with structured proposals/preview.
4. Controlled action tools through existing domain commands + explicit confirmation/audit.
5. Usage/cost observability and operational controls.
6. Future Tahfizh/Kesantrian/etc. tool expansion after those domains are production-ready.

Canonical AI architecture: `docs/08_ai/AI_ROADMAP.md`.


## Multi-module context
This Sprint 0–12 roadmap delivers the shared foundation required by the first domain plus the Academic MVP. It is **not** the implementation roadmap for Tahfizh/Kesantrian/etc. After Academic pilot acceptance, consult `MODULE_ROADMAP.md` and `codex/SYSTEM_TASK_QUEUE.md`; a future domain must first become DESIGN_READY.
