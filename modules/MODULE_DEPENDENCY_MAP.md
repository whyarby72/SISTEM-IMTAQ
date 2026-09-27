# MODULE DEPENDENCY MAP

## Dependency direction

```text
                         SHARED CORE
   Student / Guardian / Staff / Organization / shared identity contracts
                              │
          ┌───────────────────┼────────────────────┐
          ▼                   ▼                    ▼
      ACADEMIC             TAHFIZH             KESANTRIAN
          │                   │                    │
          ├─────────┐         │         ┌──────────┤
          ▼         ▼         ▼         ▼          ▼
       RUHIYAH   BAHASA   KEPENGASUHAN  KEGIATAN  ADMIN/LAYANAN
          └─────────────── domain facts ───────────┘
                              │
                              ▼
                  CROSS-DOMAIN SEMANTIC LAYER
                              │
                   ┌──────────┴──────────┐
                   ▼                     ▼
              STUDENT 360         IMTAQ AI TOOLS
                   │
                   └──── PARENT PORTAL (parent-approved artifacts only)
```

The drawing expresses logical dependency, not direct table access.

## Dependency rules

1. All domains may depend on Shared Core contracts.
2. Domain A must not directly mutate Domain B's owned transaction tables.
3. Cross-domain reporting is downstream/derived and must not become transaction authority.
4. AI is downstream of authenticated domain services/semantic services. AI never bypasses domain ownership.
5. A shared-core change is high-impact because all active modules may depend on it.
6. A module-internal change should not require edits to unrelated modules unless a published contract changes.

## Current known cross-domain dependencies

| Provider | Consumer | Contract/purpose | Status |
|---|---|---|---|
| Shared Core | All modules | canonical Student_ID / Guardian / Staff / Organization identity contracts | DESIGN_READY |
| Shared Platform | All protected paths | users / authentication / RBAC / audit | DESIGN_READY baseline |
| Academic | Future cross-domain consumers | current/historical class context via read contract | DESIGN concept |
| Shared permission service candidate | Academic/Tahfizh/Kesantrian | approved leave context; does not auto-create attendance | POLICY_PENDING details |
| Shared Alerts | Domain modules | common alert lifecycle/infrastructure; rules remain domain-owned | DESIGN_LOCKED baseline |
| Domain semantic services | Student 360 | validated derived facts | FUTURE |
| Domain tools | IMTAQ AI | authorized reads/controlled commands | FUTURE AI |
| Shared Core | Shared Communication / Parent Portal | Guardian recipient/access context; consent remains Communication-owned | DESIGN_READY |
| Source reporting services | Parent Portal | exact published parent-approved artifact/version | DESIGN_READY_FUTURE |
| Shared Communication | Parent Portal | notification/deep-link navigation only; never authorization | DESIGN_READY_FUTURE |

| Shared Core Staff/Organization/Assignments | Shared Communication | canonical internal audience resolution; provider groups are not authority | DESIGN_READY_FUTURE |
| Academic schedule/session/teacher obligations | Shared Communication | future teacher reminders/availability requests; communication response never mutates Academic directly | DESIGN_READY_FUTURE |
| Shared Communication structured responses | Academic | readiness context / operational exception input only; not teacher attendance | DESIGN_READY_FUTURE |

## Prohibited dependency patterns

- circular table ownership;
- copying student masters per module;
- using reports as source transactions;
- module-to-module direct writes;
- AI-to-database arbitrary SQL;
- shared composite score that collapses unrelated student-development domains.

## Parent Portal dependency path

`Shared Core Guardian/Student → Shared Platform Auth/RBAC/Audit → source-domain published parent artifact → Parent Portal ← optional Shared Communication deep link`

Parent Portal does not depend on direct raw Academic/Tahfizh/Kesantrian transaction-table reads.
