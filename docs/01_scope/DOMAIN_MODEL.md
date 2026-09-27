# 03 — Domain Model

## Main relationship chain

`Student → Enrollment → Teaching Assignment → Schedule Rule → Class Session → Participants → Attendance`

Parallel grade/report chain:

`Student + Semester + Subject → Semester Subject Grade → Report Card / Academic History → Transcript`

## Core master grains
- Student: 1 canonical person record.
- Student Identifier: 1 administrative identifier occurrence/version for a student.
- Student Status History: 1 effective status interval.
- Grade Level: 1 configurable academic level (for example Tingkat 1/2/3), independent from section.
- Class/Rombel: 1 academic-year-specific grade level × section record (for example 1 A, 1 B).
- Student Class Enrollment: 1 student × class × effective interval.
- Homeroom Assignment: 1 class × staff × effective interval.
- Teaching Assignment: 1 teacher × class × subject × effective period.
- Academic Calendar Event: 1 calendar exception/event × scope × period.

## Academic transaction grains
- Schedule Rule: 1 recurring academic scheduling rule.
- Class Session: 1 actual/planned KBM meeting.
- Schedule Change: 1 approved/reviewed change event.
- Schedule Conflict Result: derived validation result for one candidate resource × concrete occurrence overlap; not a Source-of-Truth transaction table.
- Teacher Participation: 1 teacher × class session.
- Student Participant: 1 student × class session expected/removed participant.
- Student Attendance: at most 1 attendance record per expected student participant.
- Semester Subject Grade: 1 student × subject × semester.
- Report Card: 1 student × semester × report type; multiple versions allowed.
- Transcript Line: 1 published transcript version × one official semester-subject grade snapshot.
- Alert: 1 deduplicated active condition occurrence with rule/version/owner/state.

## Student Attendance status vocabulary
- PRESENT
- LATE
- SICK
- PERMISSION
- EXCUSED
- ABSENT

Definitions:
- PRESENT: attended the session.
- LATE: attended but recorded late.
- SICK: absent because of sickness.
- PERMISSION: absence categorized as approved/recognized permission according to policy.
- EXCUSED: academically excused absence that is not SICK/PERMISSION; exact operational examples are POLICY_PENDING.
- ABSENT: explicit unexcused/other absence after required attendance is known; never inferred from missing data.

## Session status vocabulary
- PLANNED
- CONFIRMED
- COMPLETED
- CANCELLED
- RESCHEDULED

## Student participant status
- EXPECTED
- REMOVED

Participant basis:
- CLASS_ENROLLMENT
- SELECTED
- MANUAL_APPROVED

## Teacher participation
Role:
- PRIMARY
- SUBSTITUTE

Attendance status:
- PRESENT
- LATE
- EXCUSED
- ABSENT

Obligation type:
- ORIGINAL_SCHEDULE
- APPROVED_SWAP
- APPROVED_RESCHEDULE
- SUBSTITUTION
- EXTRA_SESSION

`SUBSTITUTE` is a role, not attendance status.

## Report artifacts
Published reports/transcripts are immutable snapshots. Correct source facts first, then regenerate/reissue artifacts.

## Class structure invariant
Class hierarchy is data-driven: `Academic Year → Grade Level → Class/Section`. Section codes such as A/B/C are not hard-coded enums. A class is year-specific, while Student_ID remains stable across promotion/transfer. See `../02_architecture/CLASS_MASTER_AND_ENROLLMENT.md`.
