# Shared Core Architecture v1.0

**Status:** `DESIGN_LOCKED` for development architecture unless a higher-priority institutional decision changes it.

## 1. Purpose
Shared Core provides the canonical identities and institutional context that every domain reuses. It exists to prevent duplicate masters, broken history and module-specific identity silos.

It is not a reporting module and not a transaction god-table.

## 2. Owned concepts
Shared Core owns:
- Student canonical identity and permanent `student_code`.
- Student administrative identifier history (`NIS`, `NISN`).
- Student lifecycle/status history.
- Guardian identity.
- Student ↔ Guardian relationships.
- Guardian contact-point history and verification metadata.
- Staff canonical identity and staff lifecycle context.
- Organizational-unit hierarchy and staff organizational assignment history.
- Shared physical locations.
- Cohort/admission context where used institutionally.
- Institutional academic-year reference used cross-domain.

Shared Platform owns and exposes to Core/domains:
- `users` / authentication runtime.
- roles, permissions and effective role assignments.
- authorization policy execution.
- append-only audit infrastructure.
- generic correction-request infrastructure.
- queues/jobs, secrets, logging and technical observability.

## 3. Domain-owned concepts explicitly excluded
Shared Core does **not** own:
- Academic class attendance, grades, schedules or report cards.
- Tahfizh halaqah transactions, ziyadah, murajaah or tasmi facts.
- Kesantrian discipline/case transactions.
- Kepengasuhan sensitive coaching notes.
- Ruhiyah observation transactions.
- Parent-report content.
- AI-generated summaries.
- WhatsApp message delivery facts.

Domains link their facts to canonical IDs but retain transaction ownership.

## 4. Dependency direction

```text
                      SHARED PLATFORM
              Auth / RBAC / Audit / Jobs
                         ▲       ▲
                         │       │
                         │       │
                     SHARED CORE
      Student / Guardian / Staff / Organization / IDs
                         │
        ┌────────────────┼────────────────┐
        ▼                ▼                ▼
    ACADEMIC          TAHFIZH         KESANTRIAN
        │                │                │
        └──────── validated domain facts ─┘
                         │
                         ▼
               Shared Reporting / AI /
                  Communication Layer
```

## 5. Core invariants
- `CORE-001` One canonical Student record per real student.
- `CORE-002` `student_code` is permanent and never reused.
- `CORE-003` Names, NIS and NISN are not primary identity keys.
- `CORE-004` Unknown administrative identifiers remain NULL/no row.
- `CORE-005` Identifier correction never creates a new Student.
- `CORE-006` Student exit never deletes historical facts.
- `CORE-007` Current status/class/assignment is derived from effective-dated history, not duplicated current columns on Student.
- `CORE-008` Guardian relationship belongs to Student↔Guardian bridge, not the Guardian person record.
- `CORE-009` Contact verification is not equivalent to communication consent.
- `CORE-010` Staff business identity is separate from user account identity.
- `CORE-011` Job title/organizational assignment is not equivalent to RBAC permission.
- `CORE-012` Domains cannot direct-update Shared Core tables; mutations go through Core commands/services.
- `CORE-013` Shared Core does not direct-update domain transaction tables.
- `CORE-014` High-value identity/lifecycle changes are audited and version/effective-date aware.
- `CORE-015` Full institution-wide authority is subject to business workflow; `SUPER_ADMIN` is not an automatic approver.

## 6. Source of truth matrix
| Concept | Source of Truth | Typical consumers |
|---|---|---|
| Student identity | `students` | all domains |
| NIS/NISN | `student_identifiers` | authorized administration/reporting |
| Student lifecycle | `student_status_history` | eligibility, reporting, migration |
| Guardian identity | `guardians` | parent reporting/portal/communication |
| Student-guardian relationship | `student_guardian_relationships` | parent eligibility |
| Guardian contact | `guardian_contact_channels` | communication/portal verification |
| Staff identity | `staff` | assignments, users, reporting |
| Staff organizational context | `staff_organizational_assignments` | authorization/ownership resolution |
| Organization hierarchy | `organizational_units` | scope, reporting, ownership |
| User account | Shared Platform `users` | auth/API/AI |
| Roles/permissions | Shared Platform RBAC | all protected paths |
| Audit | Shared Platform `audit_logs` | governance/auditors |

## 7. Design amendment from Base Engine v1.0
The Base Engine originally placed NIS/NISN/current class/current status directly on `students`, relationship on Guardian, and homeroom on class. The current architecture refines these into effective-dated/history-aware structures. This is intentional and already recorded by the Academic consistency amendments; this Shared Core specification makes those refinements system-wide.

## 8. Decision gate for future Core changes
Before changing Shared Core, Codex must state:
1. problem;
2. canonical data affected;
3. owner/inputter/validator/approver;
4. modules consuming the contract;
5. migration/backward-compatibility impact;
6. security/privacy impact;
7. regression scope;
8. whether the change is `SHARED_CORE`, `DATABASE_GLOBAL` or `SECURITY_GLOBAL`.

A Shared Core change is never treated as a module-internal refactor by default.
