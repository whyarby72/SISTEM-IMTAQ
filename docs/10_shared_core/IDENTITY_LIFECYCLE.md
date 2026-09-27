# Student Identity & Lifecycle v1.0

## 1. Identity vs administrative attributes
Canonical identity:
`students.id` + permanent `student_code`.

Administrative identifiers such as NIS/NISN can be missing, added, corrected or superseded without changing Student identity.

## 2. Student creation workflow

```text
CreateStudentDraft/Input
   ↓
duplicate/identity checks
   ↓
required master validation
   ↓
Create canonical Student
   ↓
assign permanent student_code
   ↓
audit
```

Exact institutional creator/validator remains governed by approved SOP; baseline owner is Core/Sekretariat according to Base Engine ownership.

## 3. Identifier add/correction

```text
Student exists
   ↓
AddStudentIdentifier / CorrectStudentIdentifier
   ↓
validate type/value/scope
   ↓
check active uniqueness
   ↓
old record SUPERSEDED if correction
   ↓
new ACTIVE record
   ↓
audit lineage
```

Never overwrite an NIS/NISN silently and never create a second Student to resolve an identifier correction.

## 4. Duplicate candidate handling
- Exact active Student Code collision: hard block.
- Exact active NISN collision: hard block + review.
- Name match only: candidate signal, never automatic merge.
- NIS collision: apply institutional scope rule.
- Legacy source ambiguity: quarantine until human resolution.

No automatic student merge is in MVP.

## 5. Student status lifecycle
Status is effective-dated history.

```text
ACTIVE
  ├── GRADUATED
  ├── WITHDRAWN
  ├── TRANSFERRED_OUT
  ├── DISMISSED
  └── DECEASED
```

Status transition does not delete domain facts. Domains use current status/effective date to decide **future eligibility**, not to rewrite past transactions.

## 6. Exit propagation contract
When status changes from ACTIVE to an exit status:
1. Core records new status interval and official decision reference where available.
2. emits/dispatches a controlled `StudentStatusChanged` domain event.
3. consumers evaluate future assignments/participants.
4. Academic future unlocked session participants may become `REMOVED` with explicit reason.
5. historical attendance/grades/report snapshots remain unchanged.

Shared Core does not direct-update all consumer tables itself.

## 7. Re-entry/re-activation
If institution permits a former student to return, do **not** create a new Student unless it is demonstrably a different real person. Add a new effective lifecycle interval and new assignments. Exact institutional re-entry policy is `POLICY_PENDING` until needed.

## 8. Name/profile correction
Basic identity attributes may be corrected on the canonical Student row with optimistic concurrency + audit. Published report/transcript snapshots remain immutable.

## 9. Destructive deletion
Normal deletion of a Student with business history is prohibited. Test/support fixtures may use destructive cleanup only outside business production semantics.
