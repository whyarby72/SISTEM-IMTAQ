# Decision Escalation Protocol

When a task reaches an unresolved institutional policy:

1. Stop the policy-dependent behavior; do not guess.
2. Mark the task `BLOCKED_POLICY` in `TASK_QUEUE.md`.
3. Create a decision request using `templates/POLICY_DECISION_TEMPLATE.md`.
4. Cite the affected policy ID from `POLICY_PENDING_REGISTER.md`.
5. Explain the minimum available options and implementation impact without pretending any option is already approved.
6. Continue only with unrelated unblocked tasks if dependencies remain valid and the user has not asked to focus exclusively on the blocker.
7. When a decision is approved, record it in `project_management/DECISION_LOG.md`, update the policy register/authoritative SOP as appropriate, add/activate tests, then implement.
