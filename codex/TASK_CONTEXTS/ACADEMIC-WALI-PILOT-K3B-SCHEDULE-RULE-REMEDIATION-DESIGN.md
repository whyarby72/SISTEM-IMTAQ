# Task Context — K3B Schedule Rule Remediation Design

**Task ID:** `ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-REMEDIATION-DESIGN`  
**State:** `COMPLETED / HOLD / SESSION_PROVENANCE_DIVERGENCE`  
**Mode:** read-only remediation design and dry run  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Entry HEAD:** `d933a24dd7faf3518538bbee721ea0311244cc54`  
**Entry CI:** `36806464203` — SUCCESS
**Final evidence HEAD:** `f29763e739cc360821749c7cbfc60266adcf459b`
**Final CI:** `36809446297` — SUCCESS

## Result

The current canonical scope is joint `IMTAQ-2026-2B + IMTAQ-2026-3B` for
all 14 K3B-scoped rules. The earlier `0` rule count is explained by querying
the teaching-assignment anchor instead of effective schedule-rule groups.
The two apparent overlap pairs have disjoint recurrence sets and require no
rule change.

The checkpoint remains HOLD because the read-only PILOT currently has 12
reportable K3B joint sessions, while the entry evidence says 0 sessions, and
the inspected audit log does not prove the session-generation event. Do not
execute K2B provisioning or session repair until this divergence is audited.

## Required read-only evidence

- exact repository and remote HEAD parity;
- PILOT identity and PostgreSQL 18.x;
- `BEGIN TRANSACTION READ ONLY` and `transaction_read_only=on`;
- effective scope counts using anchor, group, and resolver semantics;
- 14-rule deterministic matrix and recurrence intersection;
- K1/K2A/K2B/K3A/K3B aggregate PRIMARY baseline;
- lock/correction/session provenance aggregate;
- rollback with no database write.

## Forbidden

Rule/group mutation, session generation, K2B provisioning, attendance or
teacher-participation writes, roster/account/role changes, source/test,
migration/schema/config, AI/provider, staging/production, deployment, and
main merge.

## Authoritative evidence

- Review: `codex/REVIEWS/ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-REMEDIATION-DESIGN-2026-10-01.md`
- Entry review correction: `codex/REVIEWS/ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-DRIFT-RECONCILIATION-2026-10-01.md`
- Final CI: `https://github.com/whyarby72/SISTEM-IMTAQ/actions/runs/36806464203`

## Next

`RETURN_TO_CHATGPT_FOR_K3B_REMEDIATION_DESIGN_AUDIT`

`K2B_CONTROLLED_WRITE_GATE = HOLD` remains authoritative.
