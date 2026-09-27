# Shared Communication Future UAT Matrix v1.0

**Status:** `DESIGN_READY_FUTURE`; tests activate only when implementation is authorized.

| ID | Scenario | Expected | Priority |
|---|---|---|---|
| COM-UAT-001 | Academic holiday announcement created before canonical calendar change | send blocked or requires canonical source according to approved workflow | P0 |
| COM-UAT-002 | Canonical Academic holiday/block exists | eligible announcement may be generated | P1 |
| COM-UAT-003 | Waka Academic targets Academic teachers | only in-scope recipients resolved | P0 |
| COM-UAT-004 | Waka Academic attempts institution-wide audience without permission | DENY | P0 |
| COM-UAT-005 | Executive read-only user attempts broadcast | DENY by default | P0 |
| COM-UAT-006 | Driver group member assignment ends before send | former member excluded from dynamic/current audience | P1 |
| COM-UAT-007 | Same scheduler run executes twice | no duplicate logical reminder/request | P0 |
| COM-UAT-008 | Teacher confirms tomorrow availability | response = CONFIRMED; teacher attendance remains unchanged | P0 |
| COM-UAT-009 | Teacher responds UNAVAILABLE | operational exception created/routed; no automatic absence/substitution/reschedule/cancel | P0 |
| COM-UAT-010 | Teacher gives no response | state may become pending/no-response per approved policy; never ABSENT | P0 |
| COM-UAT-011 | Provider send fails | source schedule/calendar/report unchanged | P0 |
| COM-UAT-012 | Provider webhook replayed | idempotent; no duplicate response/delivery fact | P0 |
| COM-UAT-013 | Response arrives for wrong/closed request reference | rejected/quarantined; no domain mutation | P0 |
| COM-UAT-014 | Manual group membership changed after historical send | historical recipient snapshot unchanged | P0 |
| COM-UAT-015 | Parent report batch contains multiple students | each outbound row bound to exact Student+Guardian+artifact version | P0 |
| COM-UAT-016 | AI drafts announcement | remains draft; AI cannot decide cancellation/send without authorized human/business state | P0 |
| COM-UAT-017 | Provider group has an extra person not in institutional audience | extra provider member not automatically treated as eligible recipient | P0 |
| COM-UAT-018 | Teacher confirmed then later session cancelled canonically | cancellation remains Academic truth; confirmation history retained | P1 |
| COM-UAT-019 | Substitute approved after primary UNAVAILABLE | replacement obligation created by Academic workflow; optional new confirmation follows future policy | P1 |
| COM-UAT-020 | Recipient count preview differs from final due to assignment change before send | final send-time eligibility snapshot governs and difference is auditable | P1 |

## Activation rule
Timing, reminder cadence, no-response deadline, sender approval, final group vocabulary and provider-specific response capabilities may remain `BLOCKED_BY_POLICY` until their policy IDs in `POLICY_PENDING_REGISTER.md` are approved.
