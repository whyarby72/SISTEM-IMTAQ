# Guardian & Parent Context Master v1.0

## 1. Purpose
Provide one canonical Guardian identity and explicit Student↔Guardian relationship so Parent Portal, parent reporting and personalized communication can resolve the correct recipient without duplicating guardian data inside each domain.

## 2. Fundamental distinction

```text
Guardian Person
      ≠
Relationship to Student
      ≠
Contact Channel
      ≠
Communication Consent
      ≠
User Account
```

Each is a different fact with a different lifecycle.

## 3. Guardian identity
`guardians` identifies the person. A single Guardian may relate to multiple students.

Do not create one Guardian row per child when the same person is verifiably the same person.

No automatic fuzzy merge based on name/phone.

## 4. Relationship
`student_guardian_relationships` answers:
- which Student?
- which Guardian?
- relationship type?
- effective period?
- active/ended?
- authorized to receive parent-facing reports?
- communication priority where approved?

A Guardian's relationship to one Student does not imply authorization for another Student.

## 5. Contact channels
Contact points are effective-dated/history-aware. A changed phone number does not rewrite old delivery history.

`verification_status=VERIFIED` means the channel was verified according to system procedure. It does not mean:
- opt-in to WhatsApp marketing/notifications;
- permission to see all student data;
- authorization for all communication categories.

Communication consent/preferences remain owned by Shared Communication.

## 6. Parent-report eligibility contract
A parent-facing consumer may resolve an eligible recipient only when all required conditions are satisfied:
1. Student exists.
2. Guardian exists.
3. active Student↔Guardian relationship exists for the relevant date/policy.
4. `authorized_for_parent_reports` or equivalent approved authorization is true.
5. an eligible active contact channel exists if delivery is external.
6. channel-specific consent/preference rules pass where required.
7. exact report/artifact is PUBLISHED and approved for parent distribution.

## 7. Two guardians
The system supports multiple active guardians. Recipient strategy (`PRIMARY_ONLY`, `ALL_AUTHORIZED`, or another rule) is `POLICY_PENDING` and must not be hard-coded by Codex.

## 8. One guardian, multiple children
Every delivery/report authorization remains Student-specific. The same Guardian may receive separate artifacts for multiple children.

## 9. Guardian updates
Allowed controlled commands include:
- `CreateGuardian`
- `UpdateGuardianIdentity`
- `LinkGuardianToStudent`
- `EndGuardianRelationship`
- `AddGuardianContactChannel`
- `CorrectGuardianContactChannel`
- `VerifyGuardianContactChannel`

All high-value changes are audited.

## 10. Privacy
Guardian phone, WhatsApp, email and address are `RESTRICTED` personal data by default. Ordinary teachers do not gain raw guardian contacts simply because they can view a Student.

## 11. Migration
Legacy guardian records should match by stable verified identifiers/contact evidence plus human review. Names alone do not prove identity. Unresolved matches remain quarantined.

## 12. Ownership
Base Engine baseline: Master Wali is input/admin → Sekretariat validator → Kepala Unit owner. Final role mapping remains `MANAGEMENT_APPROVAL` dependent, but implementation must support this controlled ownership rather than unrestricted domain edits.
