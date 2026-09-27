# AI Academic Assistant MVP Scope v1.0

Task: AI-A0 — Canonical AI Readiness Rebase & MVP Scope Freeze  
Date: 2026-09-19  
Status: FROZEN_FOR_AI-A1

## Authority and baseline

This artifact is a governance freeze for a read-only Waka Akademik assistant. It does not implement an AI runtime, provider call, route, migration, or database change.

Accepted baseline:

- Session Occurrence canonicalization: completed and pilot activated.
- Cutover: `2026-09-20T00:00:00+07:00`.
- Occurrence datetime authority: `class_sessions.planned_start_at`.
- Migration evidence: 42 repository migrations and 42 PostgreSQL migrations reported `Ran`; batch numbers are not the migration count.
- Canonical attendance chain: `CanonicalAttendanceSemanticService` → `AttendanceSemanticMetricsService` → `AcademicRoleDashboardService` → `AcademicDashboardExportService`.
- Open governance carried forward: `MD_02_NON_ELIGIBLE_AUTHORITY`, `AUD_01_LEGACY_IMPORT_PROVENANCE`, source-authority precedence, and full attendance canonicalization.

## Frozen MVP

The only user is `WAKA_AKADEMIK`. The primary domain is Academic student attendance. The assistant is natural-language read-only decision support and explanation; it is not a source of truth, editor, recorder, publisher, disciplinary decision-maker, or autonomous intervention engine.

Supporting read context is limited to canonical student identity, effective class membership, session occurrence, attendance outcome, date/period filtering, canonical metrics, and relevant data-quality warnings.

Out of scope: grades, report cards, teacher attendance, Tahfizh, Ruhiyah, discipline, parent reports, prediction/risk scoring, write actions, correction, occurrence creation, parent publication, cross-domain agent behavior, voice, RAG, and SQL generation.

## Semantic rules

AI tools delegate all attendance calculations to the canonical backend. They do not reimplement denominators or rates. Pre-cutover sessions use accepted legacy reproducible semantics; sessions at/after cutover require canonical `HELD` opportunity semantics. A period crossing the cutover is returned as `MIXED`; the model never performs the split itself.

`MISSING`, unresolved, reconciliation-required, and non-eligible data must not be silently converted to zero or absence. The 15 known post-cutover legacy-cancelled transition sessions remain a warning/DQ fact and are not backfilled by AI.

## Identity rule

`Student.id` is the canonical identity. Name, nickname, Arabic name, NIS, and other natural-language values are search inputs only. One exact authorized match may resolve; multiple plausible matches return `AMBIGUOUS`; no match returns `NOT_FOUND`. The first name match must never be silently selected.

## Authorization rule

The server derives the authenticated user and effective role/permission through `AcademicAuthorizationService`. MVP access is Waka Akademik only. A role supplied by the model is never trusted. Super Admin may be allowed only through the existing application authority convention for technical testing; no new privilege is introduced.

## AI-A1 gate

AI-A1 is `READY_FOR_IMPLEMENTATION` only for the five typed read tools frozen in `AI_ACADEMIC_TOOL_CONTRACTS_v1.0.md`, with the security, evidence, privacy, and evaluation conditions in the companion artifacts.

