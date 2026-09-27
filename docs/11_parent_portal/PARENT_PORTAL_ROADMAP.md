# Parent Portal Roadmap v1.0

Parent Portal is intentionally deferred from current Academic MVP implementation progress.

## Gate PP0 — Foundation available
Prerequisites:
- Shared Core Student + Guardian + relationships implemented;
- Shared Platform auth/RBAC/audit implemented;
- secure application runtime/HTTPS/session controls;
- at least one source-domain parent-facing artifact model stable.

## Gate PP1 — Account linking
- Guardian↔User bridge;
- activation/invitation flow;
- suspension/recovery;
- verification procedure approved.

## Gate PP2 — Authorization
- `PARENT_GUARDIAN` role;
- `OWN_AUTHORIZED_CHILDREN` scope resolver;
- negative IDOR/cross-child tests;
- relationship lifecycle handling.

## Gate PP3 — Academic report read-only pilot
- list children;
- list current published Academic reports;
- view exact report version;
- optional download according to policy;
- access audit.

## Gate PP4 — Communication integration
- Shared Communication sends minimal notification + secure Portal deep link;
- deep link never bypasses Portal authorization;
- delivery and Portal access observability can be correlated.

## Gate PP5 — Production hardening
- security test;
- rate limits/recovery/session policy;
- privacy review;
- backup/recovery and access-log retention;
- Guardian UAT pilot.

## Future extensions
- parent acknowledgement;
- controlled contact-change request;
- permission/administrative service requests;
- cross-domain parent development reports;
- Tahfizh/Kesantrian approved artifacts;
- read-only Parent AI grounded only in parent-visible data.
