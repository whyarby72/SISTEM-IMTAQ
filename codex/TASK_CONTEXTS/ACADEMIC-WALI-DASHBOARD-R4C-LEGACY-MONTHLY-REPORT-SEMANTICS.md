# Academic Wali Dashboard R4C — Legacy Monthly Report Semantics

**Task:** `ACADEMIC-WALI-DASHBOARD-R4C-LEGACY-MONTHLY-REPORT-SEMANTICS`
**Mode:** implementation / presentation and provenance boundary only
**Period:** July 2026 (`2026-07`)

## Contract

The monthly report is an archive over `MonthlyAttendanceSummary` and
`MonthlyStudentAttendanceSnapshot`. Its canonical provenance is
`source_type=LEGACY_MONTHLY_SNAPSHOT`, source system `IMTAQ_LEGACY`, and
monthly aggregate grain. It is not a live `ClassSession` or
`StudentAttendance` calculation. `eligible` is presented as “Kesempatan hadir
dihitung”; `non_eligible` is presented as “Dikecualikan dari denominator” and
is never presented as absence.

The dashboard remains live-only: attendance status uses live sessions and the
attendance trend uses daily transactions. The dashboard quick action,
academic/admin navigation, index/detail views, and CSV/PDF exports identify
the July report as a historical archive and state that live changes do not
automatically alter it. Publication wording describes archive approval only;
it does not certify live source authority.

## Scope boundary

Allowed: report context/provenance read model, presentation labels/notices,
export metadata, navigation wording, and focused tests. No route rename,
migration, schema/dependency/runtime configuration change, PILOT access/write,
attendance semantic change, or new certification authority.

## Required evidence

- exact disposable PostgreSQL CI for the focused and regression suites;
- source type/system/period and non-live wording on all report surfaces;
- contradictory provenance fails closed without selecting one source silently;
- live dashboard remains isolated from this archive;
- read-only report GET/export behavior and existing authorization remain intact.

## Routing

After exact green CI, return to ChatGPT/project owner for the R4C audit. Do not
start R4D, replacement-teacher governance, Human UAT, PILOT work, Grade G3,
or AI work automatically.
