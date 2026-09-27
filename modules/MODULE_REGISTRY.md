# MODULE REGISTRY — SISTEM IMTAQ

## Architectural model

`SISTEM IMTAQ = one application + one PostgreSQL + one authentication system + explicit module boundaries.`

Canonical rule:

> A module may write only the transactions it owns. Cross-module consumers use contracts/services/views; they do not silently update another module's tables.

## Baseline domain modules

| Code | Module | Current status | Planning | Transaction ownership |
|---|---|---|---:|---|
| `ACADEMIC` | Akademik | `DESIGN_READY` | 100% | classes, teaching/schedule/session, student attendance, semester grades, academic reports/transcript |
| `TAHFIZH` | Tahfizh | `NOT_PLANNED` | 0% | future halaqah/session/ziyadah/murajaah/tasmi and related facts |
| `KESANTRIAN` | Kesantrian / Adab & Kedisiplinan | `NOT_PLANNED` | 0% | future activities, discipline, follow-up/cases within approved privacy rules |
| `RUHIYAH` | Ruhiyah / Ibadah Terobservasi | `NOT_PLANNED` | 0% | future observable worship/activity facts only; never score inner faith/ikhlas |
| `KEPENGASUHAN` | Kepengasuhan | `NOT_PLANNED` | 0% | future restricted coaching/care records with stricter privacy |
| `BAHASA` | Bahasa | `NOT_PLANNED` | 0% | future language activities/observations/results |
| `KEGIATAN_KOMPETENSI` | Kegiatan & Kompetensi | `NOT_PLANNED` | 0% | future event participation, achievements and observable competencies |
| `ADMINISTRATIF_LAYANAN` | Administratif & Layanan | `NOT_PLANNED` | 0% | future administrative/service transactions |

Machine-readable source: `MODULE_REGISTRY.json`.

## Shared workstreams

Shared workstreams are not extra student-development domains and are excluded from the eight-domain delivery denominator to avoid double counting.

| Shared area | Status | Purpose |
|---|---|---|
| Shared Core | `DESIGN_READY` | Canonical Student, Guardian, Staff, Organization identities/relationships + shared contracts; Auth/RBAC/Audit runtime remains Shared Platform |
| Shared Platform | `PRE_IMPLEMENTATION` | Laravel/PostgreSQL/runtime/logging/jobs/backup/security infrastructure |
| Cross-Domain Reporting / Student 360 | `FUTURE` | Derived consumer views across validated domain facts |
| Shared AI | `DESIGN_READY_FUTURE` | Optional AI provider/orchestrator/tool/eval layer |
| Shared Communication | `DESIGN_READY_FUTURE` | Parent delivery + institutional announcements/reminders/action requests, staff audiences/groups, queue, WhatsApp provider, structured responses and audit |
| Parent Portal | `DESIGN_READY_FUTURE` | Guardian-authenticated read access to exact published parent-approved artifacts; secure-link target |

## Module lifecycle statuses

`NOT_PLANNED → PLANNING → DESIGN_READY → IMPLEMENTING → PILOT → PRODUCTION_READY → ACTIVE → MAINTENANCE`

Additional statuses may be `BLOCKED_POLICY` or `PAUSED`, but must include a documented reason.

## Non-negotiable shared identity rule

Never create `academic_students`, `tahfizh_students`, `kesantrian_students`, or equivalent duplicate student masters.

All domain facts reference the canonical shared student identity.

