# Task Context — Academic Wali Dashboard R4B

**Task:** `ACADEMIC-WALI-DASHBOARD-R4B-FUNCTIONAL-COMPLETENESS`
**Type:** `P1_DASHBOARD_FUNCTIONAL_COMPLETENESS_REMEDIATION`
**Repository:** `whyarby72/SISTEM-IMTAQ`
**Branch:** `feat/super-admin-user-access-preferences`
**Entry HEAD:** `57779f099b19c09ece69515681b8ddcb973a9228`

## Scope

Remove the silent twelve-row cap from the Wali Academic dashboard period-session
read model and make its summary/filter counts authoritative over the complete
point-in-time authorized period dataset. Expand “Perlu Ditangani” to overdue and
today actionable sessions in the selected period while excluding future work.
Preserve R4A1/R4A2/R4A2.1 joint-session, temporal authorization, read-only, and
ordinary-session behavior.

## Required behavior

- Every authorized Wali session in the selected period is reachable; no
  `limit(12)` or equivalent presentation cap is semantic.
- Server-owned summary counts cover the complete collection: total, empty,
  incomplete, finalized, upcoming, occurrence-pending, due-not-started,
  needs-action, and missing expected teacher attendance.
- `needs_action` includes overdue/today occurrence, attendance, and expected
  teacher-attendance work, but excludes future sessions.
- “Sesi Hari Ini” remains today-only.
- The dashboard exposes a `needs_action` filter and CTA without changing
  attendance/reporting semantics.
- Wali point-in-time class/session/participant scope and Waka full authority
  remain unchanged.

## Required validation

- focused Wali dashboard tests for >12 sessions and hidden session 13+;
- complete summary/filter count parity;
- overdue + today actionable sessions and future exclusion;
- occurrence-pending actionability;
- missing expected teacher attendance actionability;
- empty period state;
- existing R4A1/R4A2/R4A2.1/R4A2.1E regression;
- PHP lint, Pint, view cache, structure guard, and exact disposable PostgreSQL
  GitHub Actions verification.

## Safety boundary

No migration, schema, dependency, runtime configuration, PILOT/staging/
production database access or write, AI/provider mutation, deployment, Grade G3,
or human UAT. Preserve `PUBLIC_ACADEMIC_AI=OFF`, `IMP-S12-007=NOT_STARTED`,
`SOC-MD-06`, and the pre-existing untracked `codex/AUDITS/` directory.

## Closeout routing

On exact CI success: `ACADEMIC_WALI_R4B_IMPLEMENTED_PASS`, candidate
`R4B_P1=READY_FOR_CHATGPT_AUDIT`.

Next atomic task:
`RETURN_TO_CHATGPT_FOR_ACADEMIC_WALI_DASHBOARD_R4B_AUDIT`.
Do not start R4C, human UAT, PILOT work, Grade G3, or AI automatically.
