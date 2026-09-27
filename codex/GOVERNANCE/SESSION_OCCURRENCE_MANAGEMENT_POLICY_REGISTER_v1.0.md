# Session Occurrence Management Policy Register v1.0

## 1. Purpose

This register freezes the approved management decisions SOC-MD-01 through SOC-MD-05 for academic session occurrence. It records governance only. It does not implement occurrence fields, status changes, migrations, or application behavior.

## 2. Authority / Predecessor Gates

Authority remains IMTAQ Core Engine v1.5. Accepted predecessor state:

- Phase 2R-B2C: CLOSED / ACCEPTED.
- SOC-RDG: CLOSED / ACCEPTED.
- Current repository session vocabulary: `PLANNED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `RESCHEDULED`.
- `COMPLETED` is an attendance-finalization terminal state and does not prove actual occurrence or official `HELD`.
- Rebase direction: `SPLIT_WORKFLOW_AND_OCCURRENCE_STATE`.

Core target remains `SCHEDULED`, `HELD`, `CANCELLED`, `RESCHEDULED`; only officially `HELD` sessions relevant to eligible students may eventually form the canonical attendance denominator.

## 3. Current Operational Role Structure

```text
SUPER_ADMIN → WAKA_AKADEMIK → WALI_KELAS
```

No `ACADEMIC_OPERATOR` role is created by this register.

## 4. Teacher Offline Evidence Model

Guru Pengampu has no website access and uses the manual printed attendance process. The teacher is an offline operational evidence provider, not a direct system recorder and not a direct HELD certifier.

Responsibility separation is preserved:

```text
EVIDENCE_PROVIDER ≠ SYSTEM_RECORDER ≠ VALIDATOR
               ≠ CORRECTION_AUTHORITY ≠ PUBLICATION_AUTHORITY
```

## 5. SOC-MD-01 — Routine HELD Recording Authority

`SOC_MD_01_STATUS = DECIDED`

Routine digital recording of a session occurrence as `HELD` is performed by `WALI_KELAS`. The Wali need not personally witness every lesson; the operational evidence may originate from the Guru Pengampu offline process.

Waka Akademik provides cross-class oversight, exception authority, and correction authority. Super Admin is not the routine academic certifier.

For joint sessions:

```text
ONE_INSTITUTIONAL_SESSION → ONE_OCCURRENCE_TRUTH
```

Class-level attendance attribution may remain partitioned.

## 6. SOC-MD-02 — Teacher Self-Certification

`SOC_MD_02_STATUS = DECIDED`

Teacher website self-certification is not applicable because Guru Pengampu has no website access.

- Teacher can set HELD in system: `NO`.
- Teacher direct database input: `NO`.
- Teacher occurrence evidence role: `YES`.

## 7. SOC-MD-03 — HELD vs Attendance Completeness

`SOC_MD_03_STATUS = DECIDED`

`HELD` does not require complete student attendance entry. Occurrence and completeness are separate concepts.

Valid future conceptual state:

```text
SESSION_OCCURRENCE = HELD
ATTENDANCE_COMPLETENESS = INCOMPLETE
```

Missing attendance does not cancel the HELD occurrence. In the future conceptual denominator, `HELD × ELIGIBLE STUDENT OBLIGATION` creates an attendance opportunity; unavailable outcomes remain `MISSING`.

No fields, tables, enums, migrations, or source logic are introduced here.

## 8. SOC-MD-04 — Routine Waka Approval

`SOC_MD_04_STATUS = DECIDED`

Routine HELD recorded by Wali Kelas does not require individual second approval by Waka Akademik.

```text
ROUTINE_HELD_REQUIRES_WAKA_APPROVAL = NO
ROUTINE_HELD_OPERATIONALLY_VALID_AFTER_RECORDING = YES
```

Operationally valid is distinct from locked and published. Waka handles monitoring, cross-class oversight, exceptions, conflicting evidence, corrections, and post-validation corrections.

## 9. SOC-MD-05 — Validated Data Correction Authority

`SOC_MD_05_STATUS = DECIDED`

Waka Akademik may initiate and execute corrections to validated academic data without a second approver. Before validation, Wali Kelas may correct within authorized class scope. After validation, Wali Kelas must not silently overwrite authoritative records.

## 10. Correction Governance

```text
SILENT_OVERWRITE = FORBIDDEN
HISTORY_DELETE = FORBIDDEN
OLD_VALUE_PRESERVED = YES
NEW_VALUE_PRESERVED = YES
CORRECTION_REASON_REQUIRED = YES
CORRECTED_BY_REQUIRED = YES
CORRECTED_AT_REQUIRED = YES
VERSION_HISTORY_REQUIRED = YES
```

Published or locked data requires an auditable corrected version preserving prior history.

## 11. Attendance vs Occurrence Correction

Attendance correction and occurrence correction are separate transaction types.

- Attendance correction changes a student outcome, such as PRESENT to SICK.
- Occurrence correction changes institutional session classification, such as an incorrect HELD decision.

Both may fall under Waka Akademik correction authority after validation, but they must not be treated as the same operation.

## 12. Authority Matrix

| Activity | Guru Pengampu | Wali Kelas | Waka Akademik | Super Admin |
|---|---|---|---|---|
| Provide KBM evidence | YES, offline | receive/review | may review | not routine |
| Website access | NO | YES | YES | YES |
| Record routine HELD | NO | YES | not routine | not routine |
| Enter student attendance | no direct website entry | YES | according to authority | not routine |
| Correct before validation | NO | YES, own class scope | YES | not routine |
| Correct after validation | NO | no silent overwrite | YES | not routine |
| Correct published academic data | NO | NO | YES with audit/versioning | not routine |
| Cross-class monitoring | NO | class scope | YES | technical as needed |
| Delete correction history | NO | NO | NO | NO |

## 13. Canonical Concept Separation

```text
SESSION_WORKFLOW_STATE
≠ SESSION_OCCURRENCE_STATE
≠ ATTENDANCE_COMPLETENESS
≠ ATTENDANCE_OUTCOME
≠ PUBLICATION_STATE
```

This is policy/design direction only. No implementation is authorized.

## 14. Joint Session Rule

A joint session remains one institutional occurrence truth. Classes such as 2B and 3B may retain separate class-level attendance attribution, but they do not receive separate HELD truths for one institutional session.

## 15. Open Decision SOC-MD-06

`SOC_MD_06_STATUS = OPEN`

No policy is frozen for partial or abnormal occurrence, including late teachers, substitutes, early termination, conflicting printed evidence, attendance recorded without valid occurrence, or materially different duration. No HELD threshold or minimum minutes rule is invented.

## 16. Existing Governance Blockers

- `SESSION_OCCURRENCE_CANONICALIZATION = NOT_COMPLETED`
- `SOURCE_AUTHORITY_ENFORCEMENT = PARTIAL`
- `WAKA_SCOPE_CANONICALIZATION = NOT_COMPLETED`
- `REAL_POSTGRES_RUNTIME = NOT_VERIFIED`
- `HISTORICAL_EXCUSED_RECONCILIATION = NOT_COMPLETED`
- `CLASS_LINEAGE_GOVERNANCE = NOT_COMPLETED`
- `FOLLOWUP_DOMAIN = NOT_IMPLEMENTED`
- `MD_01_DISPENSASI = OPEN`
- `MD_02_NON_ELIGIBLE_AUTHORITY = OPEN`
- `FULL_ATTENDANCE_CANONICALIZATION = NO`

## 17. Implementation Gate

```text
SESSION_OCCURRENCE_IMPLEMENTATION_AUTHORIZED = NO
SOURCE_MUTATION_AUTHORIZED = NO
DATABASE_MIGRATION_AUTHORIZED = NO
HISTORICAL_REWRITE_AUTHORIZED = NO
```

## 18. AI Gate

```text
AI_IMPLEMENTATION_STARTED = NO
OPENAI_API_CALLED = NO
TYPED_AI_TOOLS = NOT_STARTED
OPENAI_API_INTEGRATION = BLOCKED
LLM_ORCHESTRATION = BLOCKED
AI_UI = BLOCKED
```

## 19. Policy Status

This register freezes SOC-MD-01 through SOC-MD-05 only. SOC-MD-06 remains open. The register does not change application behavior.

```text
SOC_POLICY_REGISTER_VERSION = v1.0

SOC_MD_01 = DECIDED
SOC_MD_02 = DECIDED
SOC_MD_03 = DECIDED
SOC_MD_04 = DECIDED
SOC_MD_05 = DECIDED
SOC_MD_06 = OPEN

SOC_MDG_COMPLETE = NO

ROUTINE_HELD_RECORDER = WALI_KELAS
TEACHER_WEBSITE_ACCESS = NO
TEACHER_DIRECT_HELD_CERTIFICATION = NO
TEACHER_ROLE = OFFLINE_EVIDENCE_PROVIDER
HELD_REQUIRES_COMPLETE_STUDENT_ATTENDANCE = NO
ROUTINE_HELD_REQUIRES_WAKA_APPROVAL = NO
VALIDATED_ACADEMIC_DATA_CORRECTION_AUTHORITY = WAKA_AKADEMIK
WAKA_CAN_INITIATE_CORRECTION = YES
WAKA_CAN_EXECUTE_CORRECTION = YES
SILENT_OVERWRITE = FORBIDDEN
HISTORY_DELETE = FORBIDDEN
VERSION_HISTORY_REQUIRED = YES
ATTENDANCE_CORRECTION_AND_OCCURRENCE_CORRECTION = SEPARATE_TRANSACTION_TYPES
SESSION_OCCURRENCE_REBASE_DIRECTION = SPLIT_WORKFLOW_AND_OCCURRENCE_STATE
SESSION_OCCURRENCE_IMPLEMENTATION_AUTHORIZED = NO
SOURCE_MUTATION_AUTHORIZED = NO
DATABASE_MIGRATION_AUTHORIZED = NO
FULL_ATTENDANCE_CANONICALIZATION = NO
AI_IMPLEMENTATION_STARTED = NO
OPENAI_API_CALLED = NO
```
