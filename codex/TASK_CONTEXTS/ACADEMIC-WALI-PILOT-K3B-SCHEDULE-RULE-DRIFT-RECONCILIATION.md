# K3B Schedule Rule Drift Reconciliation

**Task ID:** `ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-DRIFT-RECONCILIATION`  
**Type:** `READ_ONLY_PILOT_STATE_AND_PROVENANCE_RECONCILIATION`  
**State:** `COMPLETED / HOLD / OPTION_C`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Entry HEAD:** `8edf768a420681b2755eb5402c110b5f269467ce`

## Result

Current K3B has 14 published, non-archived rules effective in the frozen
horizon and 0 sessions. Read-only evidence found two same-class-scope overlap
pairs and a scope mismatch: every current rule has one K3B group while its
teaching assignment is anchored to K2B.

Decision: `K3B_SCHEDULE_RULE_DRIFT_INVALID_OR_CONFLICTING`.

## Guardrails

- PILOT identity must be proven before every future read;
- all database reads use `BEGIN TRANSACTION READ ONLY` and prove
  `transaction_read_only=on`;
- no schedule/session generation;
- no schedule-rule, session, roster, attendance, participation, lock,
  correction, account, role, AI/provider, or schema mutation;
- K2B provisioning remains blocked;
- `SOC-MD-06`, `IMP-S12-007=NOT_STARTED`, and Public Academic AI OFF remain
  unchanged.

## Evidence reference

`codex/REVIEWS/ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-DRIFT-RECONCILIATION-2026-10-01.md`

## Next routing

Return this decision to ChatGPT/project-owner audit. Do not reopen K2B until
an authorized K3B remediation decision exists and a fresh mandatory preflight
is performed.
