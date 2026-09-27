# Message Template Policy v1.1

**Status:** `DESIGN_READY_FUTURE`.

## Purpose
Separate institutional message content from provider transport details; support both parent-facing personalized messages and internal institutional communication without ad-hoc unsafe wording or recipient mixing.

## Template classes
Future template registry should distinguish at least:
- parent published-report delivery;
- institutional announcement;
- schedule/event reminder;
- action request;
- action-request follow-up/escalation where approved.

## Template model
Use versioned templates with controlled variables.

Parent examples:
- guardian display name;
- student display name;
- report period/type;
- secure report link;
- institutional sender identity.

Internal staff examples:
- staff display name;
- domain/unit;
- activity/session title;
- schedule date/time;
- class/subject where relevant;
- approved change reason/summary where permitted;
- action-response choices/reference.

A template version used for a historical send remains identifiable after newer wording is introduced.

## Rules
1. Templates do not create source-domain facts; variables come from validated/approved structured context.
2. User-entered free text must not bypass sender authorization, audience eligibility, privacy or publication rules.
3. Sensitive/internal notes are never inserted merely because they exist in another module.
4. Provider-required template registration/approval states are tracked separately from institutional content approval.
5. Provider policy/format constraints are external dependencies and must be re-verified before implementation/go-live.
6. Message rendering must be reproducible or preserve an adequate sent-content snapshot/hash.
7. Action-request templates must expose only approved structured response choices; free-text may supplement but not replace the response state.
8. Announcement wording must not contradict the Source-of-Truth calendar/schedule/event state.
9. A template may be reusable across recipients; do not create one template per teacher/student/guardian.

## Example conceptual templates
- `academic_report_published_v1`
- `academic_activity_cancelled_v1`
- `teacher_session_reminder_v1`
- `teacher_availability_request_v1`
