# Governance Status Vocabulary

Use these labels consistently in code comments, task records and documentation.

| Status | Meaning |
|---|---|
| `DESIGN_LOCKED` | Architecture/design decision is stable for development. |
| `MANAGEMENT_APPROVED` | Institutional policy/SOP has been formally approved by the appropriate authority. |
| `DESIGN_ASSUMPTION` | Safe temporary technical/design assumption, explicitly not a fact. |
| `POLICY_PENDING` | Institutional decision is still required; Codex must not invent a default. |
| `FUTURE` | Intentionally outside the current MVP. |
| `SUPERSEDED` | Historical decision that must not be implemented. |

`DESIGN_LOCKED` does not imply `MANAGEMENT_APPROVED`.
