# 05 — RBAC and Authorization Contract

## Active management policy — IMTAQ PILOT GOVERNANCE MODE

The system is in `DEVELOPMENT + REAL-DATA PILOT` mode. `WAKA_AKADEMIK` has full Academic authority and `SUPER_ADMIN` has full institution-wide authority across active domains. `ADMIN_AKADEMIK` is retired. Full authority does not permit silent historical overwrite, audit/version deletion, destructive reset, or unaudited historical fact deletion.

Authorization model:

`USER → EFFECTIVE ROLE ASSIGNMENT → PERMISSION → DATA SCOPE → RESOURCE STATE → ALLOW/DENY`

Organizational hierarchy describes responsibility; it does **not** automatically create permission inheritance.

## 1. Organizational role families

### Institution-wide authority
- `SUPER_ADMIN` — full authority across all enabled institution domains, including business and platform operations, subject to each domain's scope, resource state, workflow, audit and versioning rules.

### Cross-domain system operation
- `ADMIN` — institutional/system operational administration according to assigned permissions; does not automatically inherit every Waka/member transaction permission.

### Domain governance
- `WAKA_AKADEMIK`
- `WAKA_TAHFIZH`
- `WAKA_KESANTRIAN`

`WAKA_AKADEMIK` has full business authority across the Academic institutional scope. Other Waka-domain authority is not inferred by this document and remains subject to separate approved policy.

### Domain members — currently defined for Academic
- `SEKRETARIAT`
- `GURU`
- `VALIDATOR_AKADEMIK` — only where a process genuinely requires validation; not routine student attendance.
- `WALI_KELAS`
- `AUDITOR` — restricted read/audit role according to assigned scope.

Tahfizh/Kesantrian member-role catalogs remain `NOT_PLANNED` until those domain plans are approved. Codex must not invent them.

### Executive read-only
- `KEPALA_UNIT`
- `IDAROH`
- `YAYASAN`

These roles are read-only by default. They may preview/view reports and dashboards according to institutional scope and privacy classification, but do not receive create/update/finalize/approve/lock/publish transaction authority merely because they are organizationally senior.

## 2. No automatic permission inheritance

The organizational pattern may be represented conceptually as:

```text
SUPER_ADMIN      institution-wide authority
    │
   ADMIN         operational administration
    │
 ┌──┼───────────────────────┐
 ▼  ▼                       ▼
WAKA_AKADEMIK   WAKA_TAHFIZH   WAKA_KESANTRIAN
    │               │               │
Domain members  Domain members  Domain members
```

Executive oversight is separate:

```text
YAYASAN → IDAROH → KEPALA_UNIT
        executive read-only oversight
```

A higher organizational position does not imply the union of all lower-level write permissions.

## 3. Role assignment design
Users may have multiple effective-dated roles. Do not use one `users.role` string.

Example: one staff user may simultaneously have `GURU` and `WALI_KELAS`; the permission/scope used depends on the action being performed.

## 4. Scope vocabulary
- `SELF`
- `ASSIGNED_SESSION`
- `ASSIGNED_CLASS`
- `HOMEROOM_CLASS`
- `DEPARTMENT`
- `UNIT`
- `INSTITUTION`

Future reviewed scopes may include `ASSIGNED_HALAQAH`, `ASSIGNED_STUDENTS`, `OWN_AUTHORIZED_CHILDREN`, etc.

Dynamic scope must use effective-dated business assignments.

## 5. Permission naming
`module.resource.action`

Actions include:
`view/create/update/submit/finalize/validate/approve/lock/publish/correct/cancel/export/manage`.

## 6. Executive read-only rules
`KEPALA_UNIT`, `IDAROH`, and `YAYASAN`:
- may view/preview dashboards and reports according to scope/privacy classification;
- may receive progressively more aggregated/strategic reporting as defined by reporting hierarchy;
- do not directly create/update/finalize/approve/lock/publish business transactions by default;
- do not automatically receive raw sensitive notes, correction detail, audit internals, guardian private data, biometric data, or other restricted fields merely because they can preview reports.

Read-only report access is distinct from raw transaction-table access.

## 7. Student attendance — Academic
### GURU
- Online student attendance create/update/finalize: DENY.
- May view relevant session/attendance if operationally authorized.
- Paper attendance is operational backup only.

### WALI_KELAS
Scope: `HOMEROOM_CLASS` derived from effective assignment at the session date.
- view
- create/update draft
- bulk create
- finalize
- correct validated attendance while attendance period is OPEN with reason/audit
- no direct edit after lock

### ADMIN_AKADEMIK (RETIRED)
This role is not part of active Academic authorization. Existing historical role rows and assignments remain traceable for audit, but they do not grant active authority. No automatic promotion to `WAKA_AKADEMIK` is implied.

### WAKA_AKADEMIK
Full business authority across the Academic institutional scope. This authority remains subject to resource state, correction/versioning, audit, lock and publication workflow rules.

### SUPER_ADMIN
Full institution-wide authority across enabled domains, including Academic. This does not make the actor an automatic approver or permit silent historical edits.

## 8. Homeroom handover rule
New Wali Kelas does not automatically gain historical edit rights over sessions owned by a previous effective assignment. Unfinished historical work uses explicit exception/delegation workflow with audit.

## 9. Grade access
### GURU
May enter/view grades for own teaching responsibility according to final grade workflow policy. Must not edit another teacher's grades without explicit authority.

### WALI_KELAS
May view grades for own homeroom class as needed for report preparation; cannot edit another subject teacher's score through report UI.

### ADMIN/WAKA
Monitoring/officialization/correction permissions depend on grade workflow `POLICY_PENDING`.

## 10. Report Card / Transcript
- Edit score through report/transcript UI: DENY for everyone; correct source facts instead.
- Homeroom note: Wali Kelas normal owner where workflow allows.
- Review/approve/publish authority: `POLICY_PENDING`.
- Parent future: PUBLISHED own-authorized-child artifact only.
- Executive read-only roles may preview/view according to report state/scope policy, but do not gain approval/publication permission by default.

## 11. AI permissions
Authoritative AI access policy: `docs/08_ai/AI_RBAC_ACCESS_POLICY.md`.

Baseline capability permissions:
- `ai.assistant.access`
- `ai.read`
- `ai.draft`
- `ai.action`
- `ai.platform.manage`
- `ai.usage.view`
- `ai.audit.view`

Initial default:
- `SUPER_ADMIN`: `ai.assistant.access` + `ai.read` only; draft/action remain disabled by default until later AI gates.
- all other roles: AI access DENY by default.

AI capability never replaces the underlying business permission/scope. Super Admin's full authority does not make AI an unrestricted reader/writer or allow AI to bypass normal workflow.

## 12. Security invariants
1. Backend enforces authorization; hidden buttons are not security.
2. Export is a separate permission.
3. Resource state matters: locked/published artifacts cannot be normal-edited.
4. Field-level data minimization applies; viewing student identity does not automatically reveal NISN, audit data or sensitive notes.
5. Role expiration/removal must take effect without manual data cleanup.
6. Full authority is not automatic approval; approval follows the resource workflow.
7. Organizational seniority does not imply transaction-permission inheritance.
8. Executive read-only roles remain read-only unless an explicit future policy changes a specific permission.
9. AI access never expands normal RBAC/business authority.

## 11. Shared Communication — future authorization contract
Shared Communication is `DEFERRED_FUTURE`, but its authorization boundary is reserved now.

Candidate permission families:
- `communication.announcement.view`
- `communication.announcement.create`
- `communication.announcement.send`
- `communication.reminder.view`
- `communication.reminder.manage`
- `communication.action_request.view`
- `communication.action_request.create`
- `communication.action_request.respond_self`
- `communication.audience.view`
- `communication.audience.manage`
- `communication.delivery.view`
- `communication.delivery.retry`
- `communication.template.manage`

Candidate scope examples:
- `ACADEMIC`
- `TAHFIZH`
- `KESANTRIAN`
- `UNIT`
- `INSTITUTION`
- `SELF`

Final vocabulary is future implementation detail and must align with the established `module.resource.action` permission convention.

Rules:
1. `WAKA_AKADEMIK` may later receive Academic-scoped communication rights if approved; this does not imply institution-wide send rights.
2. A teacher may later receive only `respond_self` for their own action requests without gaining broadcast permission.
3. `KEPALA_UNIT`, `IDAROH`, and `YAYASAN` remain read-only by default and receive no communication send/create permission merely from hierarchy.
4. `SUPER_ADMIN` institution-wide authority does not automatically imply communication broadcast permission; a communication permission/scope is still required.
5. Audience management is separate from message sending.
6. Delivery retry is separate from content creation/approval.
7. Provider/webhook processing runs as controlled system service authority, never as an unrestricted end-user permission.
