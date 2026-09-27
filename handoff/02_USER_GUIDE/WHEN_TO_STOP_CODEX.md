# When Codex Should Stop and Ask for a Decision

Codex should stop the affected behavior rather than guess when:

- a required rule is `POLICY_PENDING`;
- two authoritative sources conflict and source priority does not resolve it;
- a module-local request requires a Shared Core/global contract change not previously authorized;
- a destructive migration/data deletion is proposed without a safe migration/backup strategy;
- production state cannot be established safely;
- a security/privacy rule would need to be weakened;
- a future module is still `NOT_PLANNED`;
- official report/approval/threshold behavior lacks management approval.

Codex may continue unaffected tasks when the blocker is isolated.
