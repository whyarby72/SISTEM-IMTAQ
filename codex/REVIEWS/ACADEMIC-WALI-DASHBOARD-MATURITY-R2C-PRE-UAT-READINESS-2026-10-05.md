# Academic Wali Dashboard Maturity R2C — Pre-UAT Readiness

Date: 2026-10-05
Project: SISTEM-IMTAQ
Task: `ACADEMIC-WALI-DASHBOARD-MATURITY-R2C-PRE-UAT-USABILITY-HARDENING`

## Evidence basis

- Starting HEAD: `5a3fe6ebdaf84c8879e527c31a919995bebdf414`
- C2 tested executable: `91f6716aa39a51251dfa0755d7735eb04b8c15ba`
- C2 CI: `37230836045` = SUCCESS; 15 passed, 573 warnings, 2440 assertions,
  0 failed
- R2C exact CI: `37235409651` = SUCCESS on
  `aecded94649f400e558cb1bde9c5082d8784165a`; 15 passed, 575 warnings,
  2452 assertions, 0 failed
- PILOT access/write: `NONE / NONE`
- Public Academic AI: `OFF`
- Academic Web: `4/10 COMPLETE_EVIDENCED`
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`

## R2 finding disposition

| Finding | Disposition |
|---|---|
| R2-01 joint partition | CLOSED by C1/C2; HTTP finalization and dashboard partition state are covered. |
| R2-02 non-anchor authorization | CLOSED; canonical scope resolver remains authoritative. |
| R2-03 stale/double-submit recovery | CLOSED_OR_ACCEPTABLE_FOR_UAT; version guard and client in-flight guard are present; server controls remain authoritative. |
| R2-04 bulk-present safety | CLOSED; only blank editable controls are targeted and no submit/finalize occurs. |
| R2-05 GET-side materialization | KNOWN_P2 / ACCEPTED_OR_HOLD; existing bounded behavior remains and R2C adds none. |
| R2-06 mobile/device evidence | MUST_BE_VALIDATED_IN_HUMAN_UAT; source has <=680px roster/card and action styles, but no real-device claim is made. |
| R2-07 exceptional states | NON_BLOCKING; stale, pending joint partner, cancelled/read-only, and ordinary completed feedback remain explicit. |

## Usability and safety evidence

- Bulk action is disabled when editing is unavailable.
- Bulk action selects only enabled required attendance controls with blank
  values, preserving PRESENT/ABSENT/SICK/IZIN/LATE/EXCUSED values.
- The helper changes controls only; it does not call submit or finalize.
- Save Draft and Finalize are distinct buttons. A first submit disables only
  the submitting button after the submit event, sets `aria-disabled=true`, and
  shows `Menyimpan...` or `Mengesahkan...`.
- C2 stale message remains: `Data kehadiran telah berubah. Muat ulang halaman
  dan periksa kembali sebelum mengesahkan.` It is rendered through the existing
  error surface; no automatic retry was added.
- A finalized joint Wali partition continues to show `Sudah disahkan untuk
  kelas Anda`, states when the other class remains pending, and links to the
  Academic dashboard. It does not claim global completion.
- Existing ordinary Wali, joint Wali A/B, Waka, and partition-aware dashboard
  regression remains in the required CI suite.

## GET-side effect boundary

The existing `StudentAttendanceController::show()` may lazily ensure a
participant snapshot and primary teacher participation. This is not redesigned
here. No new GET-side write was introduced. Disposition:
`KNOWN_ACCEPTED_FOR_CONTROLLED_UAT`, subject to human observation and the
existing P2 architecture follow-up.

## Mobile boundary

Source review confirms the existing <=680px roster/card layout, status controls,
Save Draft/Finalize actions, bulk feedback, search, teacher attendance, and
error/toast surfaces remain present and responsive by CSS/source contract.
Real-device usability is not claimed and remains a human-UAT responsibility.

## UAT decision

`WALI_ATTENDANCE_READY_FOR_CONTROLLED_HUMAN_UAT`

No remaining source-level security, partition-integrity, or pre-UAT
usability blocker requires remediation. Device evidence and operational
behavior must still be observed by a human during the supervised UAT.

The final decision must be exactly one of:

- `WALI_ATTENDANCE_READY_FOR_CONTROLLED_HUMAN_UAT`
- `WALI_ATTENDANCE_READY_AFTER_ADDITIONAL_REMEDIATION`
- `WALI_ATTENDANCE_HOLD`

No controlled human UAT is executed automatically by R2C.
