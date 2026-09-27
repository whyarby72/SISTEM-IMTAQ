# Parent Portal Authorization v1.0

## 1. Role and scope
Future role baseline:

`PARENT_GUARDIAN`

Dynamic scope:

`OWN_AUTHORIZED_CHILDREN`

The role alone is never enough; access requires Guardian link + Student relationship + artifact authorization.

## 2. Candidate permissions
- `parent_portal.profile.view_self`
- `parent_portal.student.list_authorized`
- `parent_portal.artifact.list`
- `parent_portal.artifact.view`
- `parent_portal.artifact.download` — separate permission/policy
- future `parent_portal.artifact.acknowledge`

No generic `student.view_all`, `academic.grade.read_raw`, or `attendance.read_raw` permission is granted to Parent Portal users.

## 3. Authorization predicate
A request to view artifact `A` for Student `S` by User `U` succeeds only when all are true:
1. `U` authenticated and active.
2. `U` has effective `PARENT_GUARDIAN` role.
3. active, verified `guardian_user_account_link(U,G)` exists.
4. eligible `student_guardian_relationship(S,G)` exists according to current policy.
5. relationship/purpose authorizes parent-facing access.
6. `A.student_id = S`.
7. `A` is the exact published version requested.
8. `A` is approved for parent access.
9. requested action is allowed (view/download).
10. no suspension/revocation/access hold blocks the account.

## 4. No IDOR
Changing route parameters, UUIDs or query strings must never expose another Student's artifact. Every object retrieval is authorization-scoped server-side.

## 5. Multiple children
`GET authorized children` is derived from the Guardian's active eligible relationships. It is not a manually maintained child list in Parent Portal.

## 6. Internal content exclusion
Parent Portal permissions never inherit access to:
- internal DQ alerts;
- student Early Warning labels;
- correction requests;
- audit logs;
- internal homeroom/coaching notes;
- restricted Kesantrian/Kepengasuhan data;
- raw identifiers unless explicitly parent-required and approved.

## 7. Support/admin impersonation
No hidden “login as parent” capability in MVP. If future support impersonation is introduced, it requires explicit high-risk permission, bannered session, reason, audit and privacy review.

## 8. Access after relationship changes
Historical access retention when a relationship is ended/changed is `POLICY_PENDING`. Implementation must make this configurable/explicit rather than treating past authorization as permanent.
