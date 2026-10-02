# Super Admin User & Akses Foundation Audit — 2026-10-02

## Result

The repository now contains a dedicated User & Akses surface built on the
existing User, Staff, UserRoleAssignment, UserStaffLink, Permission, Role, and
AuditLogger contracts. It is routed under `admin.system.users` and guarded by
the server-side `platform.user_access` feature and permission.

## Evidence summary

- Account lifecycle is represented by `users.status`; login rejects
  `DISABLED` accounts.
- Staff linkage uses the existing unique `user_staff_links` contract.
- Role/scope assignment uses existing time-bounded assignments; revocation
  ends the assignment and preserves history.
- Feature access returns structured system, permission, scope, override,
  effective, and denial state. User ENABLED cannot bypass backend permission.
- Preferences are whitelisted by validation (`default_landing_page`,
  `sidebar_compact`, `table_page_size`, `show_help_text`).
- Audit events are append-only and pass through existing secret redaction;
  password values are never included.
- UI includes responsive account, role/scope, feature, preference, and empty
  states, with semantic labels and server-side route protection.

## Boundary

No PILOT data was queried or changed. No AI/provider activation, OpenAI
request, Academic UAT, or production cutover was performed. The migrations
are new and must be replayed only in disposable PostgreSQL before any pilot
migration review.

## Verification result

Exact GitHub Actions run `36944117304` on implementation HEAD
`387d7bf19305d94e188666aadf1c5ece40dcb66c` passed PHP/Composer, disposable
PostgreSQL identity, migration-from-zero, schema/extension assertions, the
focused User & Akses tests, and the full foundation verification script.

## Open gate

Controlled disposable-PostgreSQL migration replay and exact GitHub Actions
verification is complete; a future pilot migration review remains separately
gated. Existing Academic
UAT remains unchanged at `HOLD / HUMAN_OBSERVATION_REQUIRED`.
