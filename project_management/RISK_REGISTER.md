# Risk Register

| ID | Risk | Impact | Control |
|---|---|---|---|
| R-001 | Codex invents unresolved institutional policy | Wrong official workflow | `POLICY_PENDING` register, feature flags, stop conditions |
| R-002 | Old superseded attendance design returns | Duplicate/incorrect source of truth | Superseded blacklist + tests |
| R-003 | Name-based legacy matching merges wrong students | Identity corruption | Stable identifiers + human review + migration quarantine |
| R-004 | Legacy daily attendance is fabricated into sessions | False historical precision | Migration grain contract |
| R-005 | UI-only authorization leaks data | Privacy/security breach | Backend RBAC negative tests |
| R-006 | Report/Transcript becomes a second grade source | Inconsistent official data | Source correction + immutable snapshots |
| R-007 | KPI formulas diverge by screen/export | Loss of trust | Single semantic service/metric contract |
| R-008 | Scope creep to detailed assessment/AI before foundation | Delayed/unreliable MVP | MVP/FUTURE register and sprint gates |
| R-009 | Policy-blocked feature accidentally activated | Governance violation | Feature flags + blocked tests |
| R-010 | Backup exists but restore fails | Data loss risk | Mandatory restore test before rollout |
| R-011 | AI chat bypasses RBAC or exposes cross-domain/private student data | Privacy/security breach | Server-side tool allowlist + same RBAC/scope + context minimization + negative evals |
| R-012 | OpenAI/provider API key leaks through repo/browser/logs | Credential misuse/cost exposure | Server secret only, `.gitignore`, no client calls, secret rotation |
| R-013 | AI invents data or writes directly to DB | Source-of-truth corruption | No arbitrary SQL; structured tools + server validation + domain commands + confirmation |
| R-014 | Provider outage/rate limit blocks IMTAQ operations | Operational dependency | AI feature flags/fail-open-to-normal-UI; AI not transaction dependency |
| R-015 | AI cost/usage grows without visibility | Budget risk | Token/usage logs + external billing reconciliation + configurable spend/quota monitoring |
| R-016 | Vibe-coded change silently touches unrelated/shared files | Cross-module regression and difficult maintenance | Safe-change impact statement, protected zones, Minimum Necessary Change, Change Manifest |
| R-017 | Manual FTP/file-by-file overwrite leaves mixed application versions | Production inconsistency and hard-to-reproduce bugs | Git revision as release unit, repeatable deployment, staging, deployment metadata |
| R-018 | Applied migration is edited after production use | Schema history divergence / failed deploy | Applied-migration immutability; new migrations + compatibility plan |
| R-019 | Application deployment overwrites uploads/secrets/persistent artifacts | Data/credential loss | Separate persistent storage and environment secrets from release code |
| R-020 | Small fix includes broad unreviewed refactor/framework upgrade | Hidden regression / rollback difficulty | Minimum Necessary Change; separate refactor task; declared write scope |
| R-021 | Production hotfix has no commit/change record | Cannot reproduce/rollback/audit software state | Emergency changes still require Change ID, Git reconciliation, tests and Change Manifest |
| R-022 | Database rollback assumed safe for irreversible migration | Data loss / extended outage | Migration rehearsal, backup/restore plan, feature rollback/application rollback/forward-fix strategy |
