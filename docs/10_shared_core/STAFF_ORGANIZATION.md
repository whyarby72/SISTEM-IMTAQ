# Staff & Organization Context v1.0

## 1. Staff identity
A Staff record identifies a real staff member. It is not the same as:
- application User account;
- job title;
- Academic teaching assignment;
- homeroom assignment;
- Tahfizh halaqah assignment;
- RBAC role.

Stable key: `staff.id` + `staff_code`.

## 2. Organizational hierarchy
`organizational_units` represents the institutional structure in a hierarchy. Do not hard-code current staff names into schema or source code.

Possible unit types are configurable (institution, unit, department/division, section, etc.). Exact vocabulary is a governance decision.

## 3. Staff organizational assignments
Use effective-dated `staff_organizational_assignments` for organizational membership/title context.

This enables:
- history when staff changes department;
- current Department/Unit scope resolution;
- reporting ownership;
- future cross-module responsibilities.

## 4. Domain assignments remain domain-owned
Examples:
- `teaching_assignments` → Academic.
- `class_homeroom_assignments` → Academic.
- future halaqah-musyrif assignment → Tahfizh.
- future room-musyrif assignment → Kesantrian/Kepengasuhan owner as designed.

Shared Core does not collapse those into one universal assignment table.

## 5. Staff lifecycle
Do not delete a Staff record when employment/assignment ends. Close effective assignments/status and preserve historical references.

## 6. Staff ↔ User account
A Staff can have an application account when authorized. Account status and RBAC are Shared Platform concerns.

Disabling a user account does not delete Staff identity/history.

## 7. Job title does not grant permission
`role_title = Waka Akademik` (if recorded as organizational context) is not itself backend authorization. Authorization is explicitly granted via RBAC + scope and can also rely on effective business assignments.

## 8. Ownership and approval
Exact creator/validator/owner for Staff/Organization master should be approved institutionally. Codex must not infer that Academic Admin owns all Staff data.
