# Shared Core Service & Contract Registry v1.0

Domains consume stable services/contracts rather than directly coupling to Core persistence internals.

## 1. StudentIdentityContract
Purpose: resolve canonical identity.

Read operations:
- `getStudent(studentId)`
- `findStudentByStudentCode(code)`
- authorized identifier lookup where permitted

Minimum safe payload for normal domain use:
- `student_id`
- `student_code`
- display name fields required by purpose
- current lifecycle eligibility indicator/derived status when authorized

Do not include NISN or guardian contacts by default.

## 2. StudentEligibilityContract
Purpose: answer whether Student is eligible on an effective date without making the consumer interpret lifecycle tables independently.

Example:
`getStudentEligibility(student_id, at_date)` → status, eligibility flag, effective interval/reference.

Domain still owns its assignment-specific eligibility rules.

## 3. GuardianRecipientContextContract
Purpose: parent-report/communication eligibility.

Returns only authorized Guardian relationships/contact candidates for a specified Student and purpose.

It must not expose all guardians institution-wide to ordinary callers.

## 4. StaffIdentityContract
Purpose: stable Staff identity for teaching/halaqah/ownership assignments.

Payload:
- `staff_id`
- `staff_code`
- display name
- active/effective status needed by consumer

## 5. OrganizationContextContract
Purpose:
- resolve unit hierarchy;
- determine current staff organizational context;
- support unit/department scope.

## 6. AcademicYearReferenceContract
Purpose: shared institutional academic-year reference. Consumers may read active period labels/ranges. Academic owns semester/class-specific rules.

## 7. AuthorizationContract
Owned by Shared Platform.

`authorize(user, permission, resource/context)` returns allow/deny + resolved scope evidence as appropriate. Domain services must call backend authorization even if UI already hid controls.

## 8. AuditContract
Owned by Shared Platform.

Domain/Core services append structured audit entries after/within controlled transactions. Consumers do not rewrite audit history.

## 9. Core mutation commands
Only Core-owned application services should mutate Core masters:
- `CreateStudent`
- `UpdateStudentIdentity`
- `AddStudentIdentifier`
- `CorrectStudentIdentifier`
- `ApplyStudentStatusChange`
- `CreateGuardian`
- `UpdateGuardianIdentity`
- `LinkGuardianToStudent`
- `EndGuardianRelationship`
- `AddGuardianContactChannel`
- `CorrectGuardianContactChannel`
- `VerifyGuardianContactChannel`
- `CreateStaff`
- `UpdateStaffIdentity`
- `AssignStaffToOrganizationalUnit`
- `EndStaffOrganizationalAssignment`
- `CreateOrganizationalUnit`

Shared Platform commands:
- `CreateUserAccount`
- `DisableUserAccount`
- `AssignRole`
- `RevokeRole`

## 10. Event contracts
Candidate events (Laravel domain events/jobs are enough):
- `StudentCreated`
- `StudentIdentityUpdated`
- `StudentIdentifierChanged`
- `StudentStatusChanged`
- `GuardianRelationshipChanged`
- `GuardianContactChanged`
- `StaffStatusChanged`
- `StaffOrganizationAssignmentChanged`

Events notify consumers; they do not allow a consumer to overwrite the producer's master.

## 11. Compatibility
Public/shared contract changes are `MODULE_CONTRACT` or `SHARED_CORE` changes. Maintain backward compatibility or an explicit coordinated migration with consumer tests.
