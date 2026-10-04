# Academic Wali Dashboard Maturity R2 — Controlled Usability Review

**Task:** `ACADEMIC-WALI-DASHBOARD-MATURITY-R2-CONTROLLED-USABILITY-REVIEW`  
**Type:** `READ_ONLY_ROLE_WORKFLOW_USABILITY_AUDIT`  
**Review date:** 2026-10-04  
**Starting HEAD:** `319bf0368714480d98a53b9383ec982c69cc3dc6`  
**Remote parity:** verified on `feat/super-admin-user-access-preferences`  
**Source modification:** `NO`  
**PILOT access/write:** `NONE / NONE`

## Executive decision

`WALI_DASHBOARD_R2_READY_AFTER_REMEDIATION`

The R1 dashboard supplies a useful operational home for a Wali Kelas: the
assigned class, today queue, urgent counts, next session, live completion
states, and session links are exposed through the role dashboard service and
covered by focused tests. The attendance form also has a responsive roster
layout, draft/finalize distinction, progress feedback, teacher prerequisite,
and backend validation.

Controlled UAT should wait for the remediation slices below. The most
important blocker is scope integrity for joint sessions: dashboard partitioning
is evidenced, but the attendance detail controller, Wali resolver, draft
service, and finalizer still operate from the anchor `class_id` and/or the
whole session participant set. A Wali entering a joint session therefore does
not have repository evidence that the detail page and write/finalize actions
are restricted to that Wali's effective class partition.

This is a source/test review only. No browser UAT, PILOT query, attendance
write, or production-readiness claim was made.

## Review method and evidence boundary

Reviewed the R1 baseline and the following repository surfaces:

- `AcademicRoleDashboardService`, `AcademicDashboardController`;
- `academic/dashboard.blade.php` and Academic sidebar navigation;
- `StudentAttendanceController` and `academic/attendance/show.blade.php`;
- `WaliKelasContextResolver`, `AcademicAuthorizationService`;
- `StudentAttendanceDraftService`, `StudentAttendanceCompletenessChecker`,
  `StudentAttendanceFinalizer`, and `TeacherAttendanceService`;
- focused dashboard, attendance UI, completeness, finalization, teacher,
  lock, authorization, and Wali-context tests.

The review relied on source and test evidence. It did not treat a passing test
as proof of a real PILOT account, roster, schedule, or device experience.

## Persona and journey under review

Persona: mobile WALI_KELAS, one effective class, normal school day.

Target journey:

`sign in → Wali scope → dashboard → today's authorized session → attendance
draft → teacher attendance → finalize → dashboard monitoring → correction or
escalation`

### End-to-end evidence matrix

| Step | Evidence | Result |
|---|---|---|
| Sign in and session | Authenticated route middleware and existing feature tests | PASS |
| Wali scope | `waliClasses`, `WaliKelasContextResolver`, `AcademicAuthorizationService` | PASS for ordinary anchor-class path; joint scope gap remains |
| Dashboard | `AcademicDashboardController`, `AcademicRoleDashboardService`, R1 view/tests | PASS |
| Session CTA | `wali_operational` maps a session to `academic.attendance.show` | PASS for route identity; detail scope needs remediation for joint sessions |
| Student draft | `StudentAttendanceDraftService`, draft endpoint, audit/version checks | PARTIAL: robust state checks, class-partition write boundary not evidenced |
| Teacher attendance | `TeacherAttendanceService`, teacher form, primary/substitute prerequisite | PASS for ordinary session; joint scope must be carried consistently |
| Finalize | `StudentAttendanceFinalizer`, completeness checker, finalization tests | PARTIAL: backend gate is strong, but whole-session participant set is not proven class-partitioned |
| Dashboard monitoring | live Wali state labels and completion counts | PASS for dashboard evidence |
| Correction/escalation | lock, correction request/review/apply routes and tests | PASS for ordinary scoped path; joint detail scope must be included in remediation |

## Dashboard first viewport and discoverability

`AcademicRoleDashboardService::waliOperationalHome()` exposes the effective
class, active student count, today's sessions, urgent counts, completion rate,
and the next session. The session item distinguishes `DUE_NOT_STARTED`,
`DUE_INCOMPLETE`, `IN_PROGRESS`, `UPCOMING`, and `FINALIZED`, with labels such
as “Belum diisi”, “Belum lengkap”, and “Sudah disahkan”. The R1 Blade places
this operational content before lower-period analytics.

This is a good first-viewport contract for the reviewed persona. It avoids
using the dashboard as the authorization boundary: the attendance route and
services still must enforce the same effective scope.

Operational language is understandable and preserves the required semantic
distinction that missing data is not `ABSENT`. No copy change is authorized by
R2.

## Dashboard CTA to attendance context

The dashboard CTA is generated from the actual `ClassSession` instance and
uses `academic.attendance.show`. The service loads subject, teacher,
participants, teacher participation, and joint scope groups for dashboard
calculation. It partitions dashboard participants with
`participantsForDashboardClasses()` and reports class labels/counts.

The gap is downstream: `StudentAttendanceController::show()` loads
`$session->studentParticipants()` as a whole and passes all participants to the
view. `WaliKelasContextResolver` and `AcademicAuthorizationService` check the
session anchor `class_id`, not an effective scope-group partition. The route
identity is therefore correct, but the class-scoped write/read contract is not
complete for a Wali whose class participates in a joint session as a
non-anchor class.

## Attendance entry usability

### Mobile and roster density

The view has responsive rules for <=680px and <=900px, changes the input table
to cards, keeps labels through `data-label`, provides a roster search, and
uses a sticky action bar. Selects and textareas have visible focus styling;
the action bar stacks on narrow screens. This is source evidence, not a
device-level accessibility certification.

The layout is appropriate for a 20–30 student roster, but the review cannot
prove thumb reach, keyboard behavior, browser zoom, or actual scroll position
without controlled UAT.

### Status semantics

The form offers explicit statuses and a `Belum diisi` option. The dashboard
and view calculate pending/missing separately from `ABSENT`. The finalizer
rejects null status and validates controlled values. PASS for the semantic
contract.

### Bulk action

“Tandai semua hadir” is explicit, reversible before save, and tells the user
to adjust exceptional students. It still performs a broad client-side action
over every rendered attendance select. Whether this is acceptable as a
management policy for every class/joint roster is a product/safety decision,
not something this review may silently approve. Marked
`MANAGEMENT_AND_SAFETY_DECISION_REQUIRED` for R2B acceptance.

### Draft, reload, and stale state

`StudentAttendanceDraftService` distinguishes `DRAFT` from validated records,
uses row/session locks, version fields, audit events, and refuses completed
records through the normal draft path. The UI labels rows as `Draf`,
`Sudah diperiksa`, or `Belum dibuat` and displays a success/error flash.

The repository does not provide complete evidence for a mobile-level
save/reload/resume walkthrough, double-submit prevention, or a stale-page
message that identifies the exact affected row. Backend locking/version
checks reduce integrity risk, but they do not replace a clear user recovery
state.

## Completeness, teacher attendance, and finalization

`StudentAttendanceCompletenessChecker` reports required participants, missing
IDs, unresolved IDs, and `NO_PARTICIPANTS`; it does not infer absence.
`StudentAttendanceFinalizer` requires the primary teacher status, requires a
present substitute when the primary teacher is not present, locks the session
and participants, validates statuses/versions, marks attendance validated,
completes the session, and records audit entries.

The action bar exposes `filled/required`, missing count, and a blocked/ready
message. This is strong repository evidence for the ordinary class path.

The key unresolved usability/security issue is that the controller loads all
session participants and the finalizer iterates all required participants.
For a joint session, that can make a Wali's “complete” and “finalize” context
larger than the Wali's effective class partition unless an additional
class-scoped contract is added. This is P1 because it affects both user
understanding and the boundary of a write operation.

## Failure and recovery states

Evidence exists for validation and error responses covering missing students,
missing teacher participation/status, stale attendance versions, completed or
cancelled sessions, locked periods, and authorization failures. The view
renders a general error list and success toast. Correction routes separate
normal entry from post-lock correction and require audit/reviewer control.

The following recovery details are not sufficiently evidenced for controlled
UAT: per-row stale conflict recovery, explicit wrong-session/joint-scope
explanation, network retry/double-submit behavior, and a post-finalization
dashboard feedback assertion tied to a real browser interaction.

## After finalization and navigation resilience

Source/tests support a completed/read-only result view, validated workflow
state, and dashboard session state `FINALIZED` with “Lihat Hasil”. The
dashboard's urgent counts and completion state are derived from live session
data on a subsequent request.

Back, refresh, and repeated submit behavior is not fully represented as a UI
contract. Backend locks and version checks are protective, but R2 cannot call
the experience fully reconciled without a visible recovery/duplicate-submit
test.

## Joint-session review

Dashboard evidence is positive: session scope groups are loaded and participant
counts are partitioned by effective enrollment. Attendance detail evidence is
not equivalent:

- `StudentAttendanceController::show()` queries all session participants;
- `WaliKelasContextResolver::resolve()` checks only `class_id`;
- `AcademicAuthorizationService::isEffectiveWaliForSession()` checks only
  `class_id`;
- `StudentAttendanceDraftService` verifies participant belongs to the session,
  but not the Wali's effective joint partition;
- `StudentAttendanceFinalizer` finalizes every required participant on the
  session.

This is the principal R2 finding. The remediation must preserve the canonical
joint event while partitioning eligible participants and authorization by
effective class enrollment on the session date. It must not duplicate the
event or attribute all joint participants to the anchor class.

## Empty, exceptional, and accessibility states

The source contains states for no Wali assignment, no roster, cancelled or
rescheduled sessions, missing teacher, incomplete attendance, completed/read-
only results, and empty search results. The dashboard exposes “Roster belum
tersedia” where eligible participants are zero.

Evidence is insufficient to claim complete treatment of no-today-session,
locked-entry, rescheduled replacement, and joint-unmapped states at every
viewport. Existing joint breakdown labels are useful, but unmapped data should
remain a visible data-quality escalation rather than become an editable
default.

Accessibility evidence is limited to labels, `aria-live`, visible focus
styles, semantic forms/details, `aria-current`, and responsive table-to-card
labels. No WCAG conformance claim is made.

## Performance and security review

The dashboard has bounded attendance history (`limit(12)`) and R1 includes a
query-count regression. Eager loading is used for the operational queue. The
attendance detail view loads a full roster and related attendance/grooming
records, which is reasonable for a normal class but should be measured for
larger or joint rosters. Repeated calculations and the full participant form
remain a P2 UAT/performance check, not a proven production defect.

Security is strong on ordinary sessions: backend role/scope/state checks,
CSRF, lock/version controls, and audit services are present. UI hiding is not
treated as authorization. Joint-session detail scope is a P1 boundary gap
because the source evidence does not show that the same partition used by the
dashboard is enforced by read/draft/finalize actions.

The attendance GET action also calls snapshot/teacher-participation ensure
services for eligible sessions. This may be an intentional lazy-materialization
contract, but it is a surprising side effect for a read page and should be
explicitly covered or separated in R2B; classify as P2 usability/operational
clarity rather than a confirmed data-integrity defect.

## Human-error and severity register

| ID | Finding | Severity | Evidence/status |
|---|---|---|---|
| R2-01 | Joint dashboard partition is not proven to continue into attendance detail, draft, and finalize scope | P1 | Confirmed by controller/resolver/service source inspection; remediation required |
| R2-02 | Joint/non-anchor Wali authorization is anchor-class-only in attendance context checks | P1 | Confirmed source gap; must be resolved with R2-01 |
| R2-03 | No complete visible stale/double-submit/network recovery contract in the attendance UI | P2 | Backend protections exist; UI evidence incomplete |
| R2-04 | Bulk “mark all present” requires explicit management/safety acceptance for all roster contexts | P2 | `MANAGEMENT_AND_SAFETY_DECISION_REQUIRED` |
| R2-05 | GET attendance page can materialize snapshot/primary teacher participation | P2 | Confirmed source behavior; intent/UX contract not explicit |
| R2-06 | Mobile/device, keyboard, zoom, large-roster, and post-finalization feedback require controlled UAT evidence | P2 | Not a source defect; UAT evidence gap |
| R2-07 | Minor consistency/documentation follow-up for exceptional empty states and exact unresolved-item recovery | P3 | Partial source evidence; no immediate blocker |

Counts: **P0 = 0, P1 = 2, P2 = 4, P3 = 1**.

## Maturity scorecard

| Slice | Status | Basis |
|---|---|---|
| Wali dashboard discoverability | PASS | R1 operational-first dashboard, bounded queue, CTA/state tests |
| Dashboard scope and live work state | PASS | Effective Wali class and live session-state contract evidenced |
| Attendance mobile entry | PARTIAL | Responsive source and UI tests; no controlled device evidence |
| Draft/resume/stale recovery | PARTIAL | Backend/version controls pass; visible recovery contract incomplete |
| Completeness semantics | PASS | Missing remains missing; required/unresolved checks are explicit |
| Teacher attendance prerequisite | PASS | Separate teacher participation and finalization gate evidenced |
| Finalization and audit | PARTIAL | Strong ordinary path; joint participant partition not proven |
| Joint-session class safety | HOLD | Detail/read/write scope gap is activation-critical for joint classes |
| Correction/lock/escalation | PASS | Services/routes/tests evidence controlled correction path |
| Accessibility/performance | PARTIAL | Source affordances exist; real viewport/load evidence not performed |

## Remediation design slices (no implementation authorized)

| Slice | Scope | Acceptance evidence | Model |
|---|---|---|---|
| R2A | Mobile/action-bar and duplicate-submit usability review | executable narrow-viewport/keyboard/submit-state tests; no accidental status mutation | `gpt-5.6-sol`, Medium |
| R2B | Joint scope, completeness, error/recovery contract | effective enrollment partition reaches controller, draft, teacher, finalize, correction; exact unresolved messages; bulk-action decision recorded | `gpt-5.6-sol`, High for authorization/concurrency |
| R2C | Post-finalization feedback and navigation resilience | save/reload/finalize/result/dashboard state tests; stale/network/back/refresh handling | `gpt-5.6-sol`, Medium |

R2B is the recommended next atomic task because it addresses the P1 scope
boundary before usability polish. It must not duplicate joint events, broaden
Wali authority, or write PILOT data without a separately authorized task.

## UAT gate and excluded domains

`UAT_GATE = READY_AFTER_REMEDIATION`

Controlled UAT prerequisites:

1. close the joint-session class partition/authorization gap;
2. record the management/safety decision for bulk marking;
3. add executable stale/double-submit and post-finalization recovery evidence;
4. validate narrow viewport, keyboard, zoom, roster density, and real Wali
   provisioning in an authorized non-production/PILOT procedure.

PILOT UAT in this review: `NONE / NONE`.  
Public Academic AI: `OFF`.  
Grade G3: `DEFERRED_BY_OWNER_PRIORITY`.  
`IMP-S12-007`: remains `NOT_STARTED`.

## Closeout

| Item | Result |
|---|---|
| Starting HEAD | `319bf0368714480d98a53b9383ec982c69cc3dc6` |
| Final code HEAD | unchanged during review; source HEAD remains `319bf036…` |
| Source changed | `NO` |
| Dashboard discoverability | PASS |
| Attendance entry usability | PARTIAL |
| Mobile evidence | PARTIAL / controlled UAT required |
| Draft/recovery | PARTIAL |
| Completeness | PASS |
| Teacher attendance | PASS |
| Finalization | PARTIAL; joint scope remediation required |
| Failure/recovery | PARTIAL |
| Security | PARTIAL; joint scope P1 |
| P0/P1/P2/P3 | `0 / 2 / 4 / 1` |
| Recommended next task | `ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-JOINT-SCOPE-COMPLETENESS-RECOVERY` |
| UAT gate | `READY_AFTER_REMEDIATION` |
| Decision | `WALI_DASHBOARD_R2_READY_AFTER_REMEDIATION` |
| Database/PILOT mutation | `NONE / NONE` |
| AI/provider activation | `NONE; OFF` |
| SAFE_TO_CLOSE | `YES` |

No remediation was implemented. Stop here for ChatGPT audit.
