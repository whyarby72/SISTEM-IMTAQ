# Definition of Done

A feature is complete only when all applicable items are satisfied:

- Business use case/process identified.
- Canonical source and transaction grain identified.
- Schema, FK, uniqueness and domain constraints implemented.
- Authorization role + data scope implemented in backend.
- Validation and error handling implemented.
- Audit/versioning implemented where the fact is mutable or sensitive.
- Lock/correction behavior implemented where relevant.
- Data Quality behavior defined.
- Semantic/reporting impact defined; no parallel formula.
- Required automated P0/P1 tests pass.
- UAT traceability exists.
- Privacy/data-minimization reviewed.
- Backup/recovery impact considered for persistent critical data.
- Change impact/write scope was respected; no undeclared protected-zone change.
- Database migration follows applied-migration immutability and compatibility rules.
- Non-trivial code change has a completed Change Manifest with Git/release reference, tests, deployment readiness and rollback/disable path.
- Staging/smoke evidence exists where production deployment is applicable.
- Documentation/status/task queue updated.
- No `POLICY_PENDING`, `FUTURE` or `SUPERSEDED` rule was accidentally activated.
