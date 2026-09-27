# Shared Communication

Future shared platform boundary for outbound Parent/Guardian and institutional staff communications.

Owner: Shared Platform / designated Communication service owner (final institutional ownership `POLICY_PENDING`).

Consumers: Academic, Tahfizh, Kesantrian, Administratif & Layanan, future Parent Reporting/Student 360 and other approved institutional workflows.

Key responsibilities:
- parent recipient eligibility using canonical Guardian/Student relationships;
- staff/internal audience resolution using canonical Staff/Organization/Role/Assignment context;
- audited managed communication groups;
- communication purposes: `ANNOUNCEMENT`, `REMINDER`, `ACTION_REQUEST`, parent published-artifact delivery;
- consent/preferences/channel resolution where required;
- versioned message templates;
- personalized/scoped outbound batches/messages;
- structured action-request responses;
- queue/retry/idempotency;
- provider abstraction (`MessagingProvider`);
- WhatsApp adapter as first planned provider;
- delivery/response events and webhooks;
- communication audit and operational exception visibility.

Not responsible for:
- Academic/Tahfizh/Kesantrian transaction truth;
- deciding a holiday, schedule change, teacher absence, substitution, reschedule or cancellation;
- approving unpublished reports;
- exposing internal sensitive notes;
- storing provider secrets in source control;
- allowing domains to call provider APIs directly.

Critical future rule:
`Business Event First → Communication Second → Structured Response → Human Domain Decision for Exceptions`.

Teacher availability confirmation is readiness context only: `CONFIRMED ≠ PRESENT`, `NO_RESPONSE ≠ ABSENT`, and `UNAVAILABLE` never auto-mutates the Academic schedule.

Authoritative future architecture: `docs/09_communication/`.
