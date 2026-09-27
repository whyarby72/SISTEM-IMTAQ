# Communication Audience & Group Registry v1.0

**Status:** `DESIGN_READY_FUTURE`.

## Purpose
Provide a controlled way to resolve who receives an institutional announcement, reminder or action request without maintaining unmanaged phone-number lists.

## Audience resolution principle

`Audience Definition → Canonical Person/Staff Context → Eligibility → Active Contact Channel → Recipient Snapshot`

A recipient group is not itself a phone-number list. Phone/contact data is resolved at send time from the canonical contact/channel model.

## Audience classes

### Dynamic audiences
Resolved from current/effective business data.

Candidate future codes:
- `ALL_ACTIVE_STAFF`
- `ALL_ASATIDZAH`
- `ALL_MASYAYIKH`
- `ACADEMIC_TEACHERS`
- `ALL_WALI_KELAS`
- `TAHFIZH_STAFF`
- `KESANTRIAN_STAFF`
- `DRIVERS`
- `WAKA_AKADEMIK_AND_MEMBERS`
- `WAKA_TAHFIZH_AND_MEMBERS`
- `WAKA_KESANTRIAN_AND_MEMBERS`

These codes are examples of registry concepts, not an authorization to hard-code the final institutional vocabulary before Staff/Organization planning is approved.

### Explicit recipient list
Used for a one-time controlled communication when recipients are resolved to canonical `staff_id`/`guardian_id` records.

### Managed manual group
For legitimate groups that cannot be derived cleanly from normal role/organization assignments, e.g. event committee. Membership must be effective-dated and audited.

## Future logical objects
### `communication_audiences`
Suggested concepts:
- `audience_code`
- `audience_name`
- `audience_type` (`DYNAMIC_RULE`, `MANAGED_GROUP`, `EXPLICIT_LIST`)
- `owner_scope`
- `rule_reference`/configuration
- active/effective dates
- created/approved metadata

### `communication_group_members`
For managed groups only:
- group/audience id
- canonical person/staff id
- effective_from / effective_until
- membership status
- reason/source
- assigned_by / assigned_at

## Recipient eligibility checks
Before a message is queued, the platform should evaluate relevant conditions such as:
- person/staff is active/effective for the target time;
- audience rule actually matches the person's assignment/role;
- intended channel exists and is eligible;
- opt-in/consent where required by channel/purpose;
- recipient is not explicitly excluded by approved policy;
- sender has permission and scope to target the audience.

## Snapshot rule
The final outbound batch stores a recipient snapshot sufficient to audit who was targeted at the time of sending. Later role/group changes must not rewrite historical recipient evidence.

## Security rule
A sender who can message `ACADEMIC_TEACHERS` does not automatically gain the right to message `ALL_ACTIVE_STAFF` or `DRIVERS`. Audience targeting is a scoped permission.

## No group-chat dependency
WhatsApp/other provider group membership is not the institutional Source of Truth for audience membership. Provider groups may exist operationally, but SISTEM IMTAQ recipient resolution is based on canonical institutional data.
