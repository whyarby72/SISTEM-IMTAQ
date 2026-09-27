# 13 — Policy Pending Register

Codex must not choose values or workflows for these items.

## Attendance
- POL-SOP-001 attendance completion SLA.
- POL-SOP-002 monthly attendance lock timing.
- POL-SOP-003 normal attendance lock actor.
- POL-SOP-004 final post-lock attendance correction approver.
- POL-SOP-013 student late threshold.
- POL-SOP-014 partial permission treatment.

## Teacher attendance / schedule governance
- POL-SOP-005 substitution approver.
- POL-SOP-006 swap approver.
- POL-SOP-007 reschedule approver.
- POL-SOP-008 cancellation approver.
- POL-SOP-009 extra session approver.
- POL-SOP-010 teacher attendance inputter.
- POL-SOP-011 teacher self-confirm allowed?
- POL-SOP-012 teacher late threshold.
- POL-SCHED-001 minimum transition/travel buffer between adjacent teaching sessions, if any. Until approved, `[start,end)` endpoint adjacency is allowed.
- POL-SCHED-002 which locations/resources are exclusive and therefore hard-block location overlap.
- POL-SCHED-003 whether any calendar-aware exception may permit otherwise overlapping recurring schedule rules; default engine remains conservative.

## Grade workflow — highest remaining policy blocker
- Who enters semester grade?
- Does teacher finalization make it official or is second human validation required?
- Who may correct a finalized grade before closure?
- What is the grade officialization/closure state machine?
- What is the final persistence grain of grade-period locks?
- Who locks/closes grades?
- Who approves post-close grade corrections?

## Permission
- POL-PERM-001 must PERMISSION attendance always reference `permission_event_id`?
- POL-PERM-002 minimum time overlap/applicability rule.
- POL-PERM-003 permission owner/approver.
- Operational examples/boundary for EXCUSED.

## Report Card
- POL-REPORT-001 mid-semester exit report policy.
- POL-REPORT-002 reporting class after mid-semester transfer.
- POL-REPORT-003 final reviewer.
- POL-REPORT-004 publication approver.
- POL-REPORT-005 signatories.
- POL-REPORT-006 homeroom note mandatory/optional.
- POL-REPORT-007 whether EXCUSED displayed separately.
- POL-REPORT-008 decimal display.
- POL-REPORT-009 semester mean shown or not.
- POL-REPORT-010 identifier change reissue rule.
- POL-REPORT-011 official report numbering.
- POL-REPORT-012 physical/digital signature policy.

## Transcript
- retake/repeated-subject treatment.
- eligibility for withdrawn/dismissed student transcript.
- partial transcript for active students.
- generation/review/approval/publish roles.
- signatories.
- class display per semester.
- average display, if any.
- decimal display.
- reissue rules.
- document numbering.

## KPI/Early Warning
- generic Attendance % definition, if needed.
- treatment of LATE/SICK/PERMISSION/EXCUSED in generic attendance.
- Early Warning absence threshold/window.
- repeated lateness threshold/window.
- low physical presence threshold and minimum opportunities.
- alert SLA/escalation cadence.
- teacher attendance KPI activation.
- management reporting cadence.
- parent-facing metric list.

## Migration
- official go-live/cutover date.
- migration approver.
- partial batch acceptance policy.
- raw/staging retention period.
- human sample percentage.
- legacy daily attendance use in reports.
- legacy attendance parent visibility.
- historical teacher summary treatment.
- legacy document retention/use.
- rollback authority.
- unresolved ambiguous identity handling.
- priority when two legacy sources conflict.



## Shared Core / Guardian / Organization
- final institutional creator/validator/owner mapping for Student, Guardian, Staff and Organization master changes.
- former-student re-entry/reactivation policy.
- exact organization unit/type and staff assignment-title vocabulary.
- Guardian identity verification/evidence SOP.
- primary-only vs all-authorized Guardian recipient strategy remains a communication/reporting policy, not a Core hard-code.

## Communication / WhatsApp / Guardian delivery
- final institutional owner of Guardian master and communication service.
- which guardian relationship types may receive which parent-facing report classes.
- primary-only vs all-authorized-guardians delivery policy.
- contact verification requirements and responsible verifier.
- consent/opt-in/opt-out policy by channel and communication purpose.
- who may create, approve and execute bulk parent-delivery batches.
- whether report delivery uses secure portal links, PDF attachments, or another approved format.
- message-template institutional approval and sign-off process.
- resend/escalation policy for failed/unreachable parent channels.
- external WhatsApp/provider policy, template, pricing and technical requirements must be re-verified at implementation/go-live.

## Policy handling rule
Until a policy is approved:
- feature remains disabled, constrained to non-official preview, or represented by an interface/configuration placeholder;
- do not invent thresholds, approvers, maker-checker, locks, labels or publication authority;
- corresponding UAT may be `BLOCKED_BY_POLICY`.

## Parent Portal
- POL-PORTAL-001 final Guardian account activation/identity-verification method.
- POL-PORTAL-002 whether former/ended Guardian relationships retain access to historical artifacts.
- POL-PORTAL-003 whether superseded report versions remain directly visible to Guardians.
- POL-PORTAL-004 whether PDF/download is enabled and for which artifact classes.
- POL-PORTAL-005 whether parent acknowledgement of published reports is required.
- POL-PORTAL-006 whether Guardians may submit contact/master-data change requests through the Portal.
- POL-PORTAL-007 MFA requirement and eligible authentication methods.
- POL-PORTAL-008 session inactivity/maximum lifetime policy.
- POL-PORTAL-009 whether Academic Transcript is parent-visible in the Portal.
- POL-PORTAL-010 secure/deep-link expiry and resend policy.

## Institutional Communication / Broadcast / Reminder / Action Request
- POL-COMM-INT-001 final owner of Shared Communication operations.
- POL-COMM-INT-002 who may create/send Academic, Tahfizh, Kesantrian, unit-wide and institution-wide announcements.
- POL-COMM-INT-003 which manual broadcast classes require review/approval before send.
- POL-COMM-INT-004 final institutional audience vocabulary (asatidzah, masyayikh, drivers, domain members, committees, etc.).
- POL-COMM-INT-005 who may create/manage audited manual communication groups.
- POL-COMM-INT-006 whether institutional announcement acknowledgement is required for any message classes.
- POL-COMM-INT-007 allowed channels and consent/notification policy for internal staff communications.
- POL-COMM-INT-008 retention policy for internal communication content and response events.

### Teacher availability / tomorrow readiness
- POL-COMM-TEACH-001 time/window for first teacher availability request.
- POL-COMM-TEACH-002 reminder cadence for pending teacher responses.
- POL-COMM-TEACH-003 deadline/no-response state definition.
- POL-COMM-TEACH-004 owner/escalation recipient for `UNAVAILABLE` and `NO_RESPONSE`.
- POL-COMM-TEACH-005 whether every future session requires confirmation or only configured session categories/teachers.
- POL-COMM-TEACH-006 allowed response vocabulary and whether free-text reason is required for `UNAVAILABLE`.
- POL-COMM-TEACH-007 whether confirmed availability may suppress later reminders.
- POL-COMM-TEACH-008 whether substitute teachers receive a fresh confirmation request after an approved substitution.

Until these are approved, teacher confirmation/reminder/broadcast implementation remains `DEFERRED_FUTURE`; Codex must not invent timing, escalation, acknowledgement or approval defaults.
