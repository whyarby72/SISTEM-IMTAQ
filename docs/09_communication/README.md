# Shared Communication Platform

This directory defines the future shared communication layer for SISTEM IMTAQ. It is a **shared platform capability**, not an Academic-owned feature.

Current status: `DESIGN_READY_FUTURE` / implementation deferred.

The platform covers:
- personalized Parent/Guardian report delivery;
- institutional announcements;
- schedule/event-driven reminders;
- structured action requests requiring recipient responses;
- future staff audiences such as Academic teachers, Wali Kelas, masyayikh, asatidzah, drivers and domain teams;
- provider-neutral outbound delivery with WhatsApp as the first planned adapter.

Core principle:

`OFFICIAL FACT/DECISION → AUDIENCE/RECIPIENT → ELIGIBILITY → MESSAGE → QUEUE → PROVIDER → DELIVERY/RESPONSE → AUDIT`

Communication is never a Source of Truth for Academic/Tahfizh/Kesantrian facts.

Read in this order:
1. `COMMUNICATION_ARCHITECTURE.md`
2. `INSTITUTIONAL_BROADCAST_REMINDER_ACTION_REQUEST.md`
3. `AUDIENCE_AND_GROUP_REGISTRY.md`
4. `TEACHER_AVAILABILITY_CONFIRMATION.md`
5. `GUARDIAN_CONTACT_AND_CONSENT.md`
6. `PARENT_REPORT_DELIVERY_WORKFLOW.md`
7. `MESSAGE_TEMPLATE_POLICY.md`
8. `WHATSAPP_PROVIDER_ARCHITECTURE.md`
9. `DELIVERY_OBSERVABILITY_AND_AUDIT.md`
10. `COMMUNICATION_PRIVACY_SECURITY.md`
11. `COMMUNICATION_UAT_MATRIX.md`
12. `COMMUNICATION_ROADMAP.md`
