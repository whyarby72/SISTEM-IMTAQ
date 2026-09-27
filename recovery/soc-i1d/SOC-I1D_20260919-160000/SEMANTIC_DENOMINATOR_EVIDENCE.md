# Semantic Denominator Evidence

The denominator owner remains `CanonicalAttendanceSemanticService`.

- Legacy regime opportunity: legacy `session_status = COMPLETED` only.
- Canonical regime opportunity: effective occurrence status `HELD` only.
- Canonical `SCHEDULED`, `CANCELLED`, `RESCHEDULED`, missing occurrence, and legacy `COMPLETED` without `HELD` are excluded.
- Canonical `HELD` remains an opportunity even when student attendance is incomplete.
- `PARTIAL_HELD` is request semantics only; storage remains `occurrence_status = HELD` with `is_partial = true`.
- Post-cutover missing effective occurrence is returned as `missing_canonical_occurrence_sessions` for DQ handling.
- Outcome formulas and NON_ELIGIBLE authority were not changed.

