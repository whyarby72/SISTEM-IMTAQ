# Change Manifest — AI-PROVIDER-R1-C

Project: SISTEM IMTAQ  
Task: Structural Grounding & Academic Data Minimization  
Date: 2026-09-24  
Status: `PASS / READY_FOR_R1C_AUDIT`

## Scope completed

- Replaced model-text substring heuristics as grounding authority with structural tool-result evaluation.
- Added explicit server-owned grounding terminal states: `TOOL_GROUNDED`, `AMBIGUOUS`, `NOT_FOUND`, `CLARIFICATION_REQUIRED`, `CAPABILITY_RESPONSE`, `REFUSAL`, `TOOL_ERROR`, `TOOL_INCOMPLETE`, and `UNSUPPORTED_REQUEST`.
- Prevented identity-only `resolve_student` success from being treated as attendance evidence.
- Added deterministic server-owned responses for capability, unsupported write, ambiguous identity, and not-found outcomes.
- Preserved provider/tool warnings through orchestration failure paths.
- Removed `raw_attendance_status`, `reason_code`, and free-text `notes` from AI student attendance detail output.

## Attendance reason classification

`ATTENDANCE_REASON_CLASSIFICATION = FREE_TEXT_EXCLUDED`.

The source column is a nullable string without an authoritative enum/check constraint or bounded vocabulary. It is therefore excluded from AI context together with `student_attendance.notes`. Source storage, forms, correction services, exports, and business semantics were not changed.

## Safety boundaries

- Application source changed: `YES`, limited to R1-C files listed below.
- Database schema/data changed: `NO`.
- Migration/seed/import: `NO`.
- Provider DRAFT/configuration changed: `NO`.
- Credential/key created or exposed: `NO`.
- Live OpenAI request: `NO`.
- Real student data sent externally: `NO`.
- Provider/public AI activation: `NO`.
- Academic business write: `NO`.
- R1-D, AI-A5V, AI-A6 started: `NO`.

## Validation

- AI focused suite: 52 tests / 210 assertions, PASS.
- Academic suite: 383 tests / 1,553 assertions, PASS.
- Full suite: 496 tests / 2,021 assertions, PASS.
- PHP lint: PASS.
- Pint: PASS.
- View cache: PASS.
- Substring grounding authority search: PASS; legacy `isSafeNonFactualResponse` removed.

## Modified files

- `application/web/app/Domains/Academic/AI/AcademicAiGroundingStatus.php`
- `application/web/app/Domains/Academic/AI/AcademicAiOrchestrator.php`
- `application/web/app/Domains/Academic/AI/AcademicAiAttendanceReader.php`
- `application/web/app/Domains/Academic/AI/Contracts/AcademicAiRuntimeResult.php`
- `application/web/tests/Feature/Academic/AI/AcademicAiRuntimeTest.php`
- `application/web/tests/Feature/Academic/AI/AttendanceReadToolTest.php`

## Gate

`GATE_C_AFTER_R1C = BLOCKED_PENDING_R1D`  
`NEXT_ATOMIC_TASK = RETURN_TO_CHATGPT_FOR_R1C_AUDIT`
