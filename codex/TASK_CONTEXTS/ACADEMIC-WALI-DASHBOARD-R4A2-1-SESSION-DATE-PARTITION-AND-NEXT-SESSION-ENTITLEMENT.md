# Task Context — ACADEMIC-WALI-DASHBOARD-R4A2-1

## Identity and authority

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WALI-DASHBOARD-R4A2-1-SESSION-DATE-PARTITION-AND-NEXT-SESSION-ENTITLEMENT`
- Type: targeted security-boundary and read-model correction
- Starting HEAD: `4043e686685a468c6d61a2038a6cd891c1eb4195`
- Branch: `feat/super-admin-user-access-preferences`
- PILOT access/write: `NONE / NONE`
- Public Academic AI: `OFF`
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`

This task is a narrow continuation after R4A2. It does not authorize R4A3,
R4B, Grade G3, human UAT, controlled PILOT work, AI work, migrations,
schema changes, deployment, or changes to attendance finalization,
correction, lock, substitute, or session-generation semantics.

## Problem statements

### P1 — session-date joint partition

The Wali dashboard previously partitioned a joint session from the full
period class collection. A Wali could therefore receive a read model built
from a class that was not effective on the physical session date. The
canonical session class scope is the scope-group class IDs, or the anchor
class ID when no scope groups exist. The participant partition must be the
intersection of that canonical scope and the Wali's effective homeroom class
entitlements on the session's Asia/Jakarta business date.

`ClassSession.planned_start_at` is an absolute stored instant. Resolve its
business date with `AcademicBusinessTime`; never use the raw UTC date as the
business date. Effective windows are end-exclusive. Zero effective class
IDs fail closed; one or more effective IDs may read only their partition.

### P2 — future next-session entitlement

The operational dashboard's today queue remains restricted to today's
effective Wali entitlement windows. Its next-session query must use a
separate future entitlement scope so a session tomorrow or later is not
silently hidden. The future query is bounded by the active academic-year end
and remains end-exclusive at assignment boundaries. A Wali whose entitlement
starts in the future must not gain today's dashboard access. A future A→B
transition must expose the future session with the correct effective class
label; if the UI cannot represent this safely, the result is
`ACADEMIC_WALI_R4A2_1_PARTIAL / NEXT_SESSION_TRANSITION_UX_DECISION_REQUIRED`.

## Required implementation contract

- Reuse `AcademicClassScopeResolver` for canonical session scope.
- Reuse `WaliClassEntitlementResolver` for date-aware effective windows.
- Reuse `JointAttendanceRosterBreakdownService` for effective enrollment
  partitioning on the session date.
- Preserve Waka Academic and Super Admin full-session visibility.
- Preserve ordinary one-class sessions.
- Preserve `limit(12)` in the period attendance-session list.
- Keep `StudentAttendanceFinalizer` unchanged.
- Do not expose another joint class's participant roster in the Wali HTML or
  read model.
- Unmapped/ambiguous participant or class resolution remains fail-closed by
  the existing canonical resolver contracts.
- No database business write is authorized by this task.

## Required regression coverage

The disposable PostgreSQL suite must cover:

- anchor-class Wali joint-session access;
- non-anchor-class Wali access to the same physical session;
- A/B participant partition isolation in both directions;
- ordinary one-class behavior;
- attendance-session list count and class label;
- period counters and trend isolation;
- next session tomorrow/later;
- session after `effective_until` excluded;
- future A→B transition label and scope;
- future-only Wali does not gain today's dashboard;
- Waka/Super Admin legitimate full authority;
- read-only dashboard has no new attendance or teacher-participation write.

All DB-backed tests run only against disposable PostgreSQL through the
repository identity guard. The protected local `imtaq` database must be
rejected fail-closed.

## Validation and closeout contract

Required checks include PHP lint, Pint, Composer validation, view cache,
route inspection where useful, structure check, diff check, focused Academic
tests, migrate-from-zero disposable PostgreSQL, full foundation verification,
and exact GitHub Actions on the final pushed HEAD.

Only after exact CI is green may the implementation be marked
`ACADEMIC_WALI_R4A2_1_IMPLEMENTED_PASS`. If the future transition is not
safe while P1 is fixed, use the explicit PARTIAL decision. If any cross-class
leak remains, use `ACADEMIC_WALI_R4A2_1_HOLD`.

Until the independent ChatGPT/project-owner audit, R4 P1 remains `3/7`; this
task must not self-promote the broader R4 maturity gate. `SOC-MD-06`,
`IMP-S12-007 = NOT_STARTED`, Public Academic AI OFF, and Academic Web
`4/10 = 40%` remain unchanged.

## Exact implementation scope

- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Domains/Academic/Services/WaliClassEntitlementResolver.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- this task context and its change manifest/evidence routing

No migration, schema, dependency, runtime configuration, pilot data, or
production/staging data changes are permitted.

