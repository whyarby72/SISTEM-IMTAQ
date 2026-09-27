# CHANGE IMPACT RULES

Every non-trivial Codex change must be classified before implementation. This classification is executed inside the safe-change workflow defined by `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`.

## Change classes

| Class | Meaning | Minimum test/approval scope |
|---|---|---|
| `MODULE_INTERNAL` | implementation changes inside one module without changing published contracts | target-module tests + smoke regression |
| `MODULE_CONTRACT` | changes a contract consumed outside the module | provider + all known consumer contract/regression tests; compatibility plan |
| `SHARED_CORE` | student/staff/auth/RBAC/audit/shared master or common policy infrastructure | impact review across all active modules + cross-module regression |
| `CROSS_DOMAIN` | Student 360, parent reporting, shared semantic aggregation/integration | validate each source-domain contract + privacy/reporting review |
| `DATABASE_GLOBAL` | database engine/global migration/storage/backup behavior | migration/rollback/restore + impacted domain regression |
| `SECURITY_GLOBAL` | auth, authorization, privacy, encryption/session/security controls | negative RBAC/security regression across all active modules |
| `AI_PLATFORM` | provider/orchestrator/tool registry/eval/secrets shared AI behavior | AI policy/eval/security tests + affected domain tool contract tests |
| `COMMUNICATION_PLATFORM` | guardian-recipient resolution, templates, outbound queue, messaging provider/webhook/delivery behavior | communication privacy/security tests + provider/consumer contract tests + recipient isolation + delivery audit regression |
| `PARENT_PORTAL` | Guardian account linking, parent authentication surface, child-scope resolution, published artifact access/deep-link behavior | Portal negative authorization/IDOR/session/privacy tests + Shared Core/source-artifact contract regression |

A change may have more than one class. Use the highest-impact set, not the easiest label.

## Required pre-change impact statement

Before coding, record at least:
- requested problem/outcome;
- `Change Class`;
- owner module;
- affected modules/workstreams;
- source-of-truth tables/services affected;
- cross-module contracts touched;
- migration/backward-compatibility implications;
- RBAC/privacy impact;
- regression scope;
- whether management policy is required;
- expected file/write scope and protected zones;
- staging/deployment impact;
- rollback/feature-disable plan.

Use `templates/CHANGE_IMPACT_TEMPLATE.md` for medium/high-impact changes.

## Change gates

### Module-internal path
`REQUEST → IMPACT CHECK → BRANCH/ISOLATE → IMPLEMENT → MODULE TESTS → SMOKE REGRESSION → CHANGE MANIFEST → STAGING/DEPLOY → DONE`

### Shared/contract path
`REQUEST → IMPACT ANALYSIS → DEPENDENCY/CONSUMER MAP → POLICY/APPROVAL IF NEEDED → COMPATIBILITY/MIGRATION/ROLLBACK PLAN → BRANCH/ISOLATE → IMPLEMENT → CROSS-MODULE REGRESSION → CHANGE MANIFEST → STAGING/APPROVAL → DEPLOY → DONE`

## Core safety rule

A fix to one module must not silently rewrite another module's business facts. If solving the request requires that, stop and redesign the contract/workflow.
