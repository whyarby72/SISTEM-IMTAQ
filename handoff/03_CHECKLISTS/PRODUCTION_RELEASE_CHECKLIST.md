# Production Release Checklist

Do not use before the production gate is reached.

- [ ] Release passed staging.
- [ ] Exact approved Git revision/release identified.
- [ ] Production backup/restore readiness confirmed.
- [ ] Required migrations reviewed for compatibility.
- [ ] No S1/S2 integrity/security defect remains open for the release.
- [ ] Operational policy dependencies for activated official workflows are approved.
- [ ] Rollback/disable path is credible/tested.
- [ ] Deployment does not overwrite persistent uploads/secrets.
- [ ] Post-deploy smoke tests defined and executed.
- [ ] Release metadata and result recorded.
