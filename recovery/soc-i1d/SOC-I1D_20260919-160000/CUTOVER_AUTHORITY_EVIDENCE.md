# Cutover Authority Evidence

- Authoritative occurrence datetime: `ClassSession.planned_start_at`, with `datetime` cast.
- Central resolver: `SessionOccurrenceCutover`.
- Regimes: `LEGACY`, `CANONICAL`, and `UNCONFIGURED`.
- Boundary: `planned_start_at < cutover_at` is legacy; `planned_start_at >= cutover_at` is canonical.
- Gate OFF always preserves legacy operational semantics.
- Gate ON with missing or invalid timestamp returns `UNCONFIGURED` and canonical mutation fails closed.
- No real pilot timestamp is configured.

