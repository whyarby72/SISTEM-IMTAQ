# CROSS-MODULE CONTRACTS

Contracts are the stable boundary between modules. A contract may be a backend service interface, explicit API, semantic query service, event, or read view. It is not permission for arbitrary cross-table access.

## Core contracts

### `StudentIdentityContract`
Provider: Shared Core

Minimum concepts:
- canonical `student_id` / permanent `student_code`;
- active/effective student status;
- approved identifiers according to field-level permission;
- no module-specific duplicate student identity.

### `StaffIdentityContract`
Provider: Shared Core

Provides canonical staff identity and effective role/assignment context without allowing domains to redefine the staff master.

### `AuthorizationContract`
Provider: Shared Core

Every UI/API/AI path must enforce backend RBAC + dynamic data scope. A consumer may not loosen permissions inherited from the underlying domain.

### `AuditContract`
Provider: Shared Core

High-value state changes append auditable actor/time/reason/version/correlation information. Domain modules own business meaning; Shared Core owns common audit infrastructure.

## Operational/shared-context contracts

### `StudentClassContextContract`
Provider: Academic

Purpose: allow authorized other modules to read a student's effective class context when operationally relevant. Other modules must not update Academic enrollment through this contract.

### `PermissionContextContract` — POLICY_PENDING details
Shared service candidate.

An approved permission event can be consulted by multiple domains, but permission context does not automatically create Academic/Tahfizh/Kesantrian attendance facts.

### `AlertInfrastructureContract`
Provider: Shared platform

Common alert lifecycle/status/action/dedup infrastructure. Each domain owns the rules and evidence that produce its alerts.


### `GuardianRelationshipContract` — FUTURE
Provider: Shared Core / designated administrative master owner.

Provides effective Student ↔ Guardian relationships and authorization flags for parent-facing purposes. It does not grant a domain permission to modify guardian master data.

### `CommunicationEligibilityContract` — FUTURE
Provider: Shared Communication.

Resolves whether a specific Guardian/contact channel is eligible to receive a specific class of parent communication for a specific Student at send time, including relationship, authorization, channel validity, consent/preference and policy checks.

### `PublishedArtifactDeliveryContract` — FUTURE
Providers: source reporting/domain services; consumer: Shared Communication.

Exposes only an exact published/approved parent-facing artifact/version plus minimally necessary rendering metadata. Delivery never edits the artifact or source transaction.

### `MessagingProviderContract` — FUTURE
Provider: Shared Communication.

Provider-neutral outbound transport boundary. Domain modules must not call WhatsApp/provider APIs directly. Provider/webhook state is transport metadata, not source-domain truth.

## Cross-domain consumer contracts

### `Student360FactContract` — FUTURE
Each module exposes approved, validated, minimally necessary semantic facts to a derived Student 360 service. Student 360 stores/serves derived views and snapshots where appropriate; it is not the original source of truth.

### `ParentReportFactContract` — FUTURE
Only explicitly parent-approved facts (`approved_for_parent_report` or equivalent policy) may enter a cross-domain parent report. Internal sensitive notes are excluded by default.

### `AIToolContract` — FUTURE
AI tools call authenticated domain services with structured inputs/outputs. Tool authorization never exceeds the current user's normal domain scope.

## Contract-change rule

Any breaking change to a published cross-module contract is `MODULE_CONTRACT` impact at minimum and requires:
1. provider impact analysis;
2. consumer inventory;
3. compatibility/migration plan;
4. provider + consumer contract/regression tests;
5. explicit version/change record.

Do not silently rename/remove contract fields used by another active module.


## Shared Core v1.0 contract authority
Detailed payload, mutation and compatibility rules are authoritative in `docs/10_shared_core/CORE_SERVICE_CONTRACTS.md`. Shared Core now explicitly defines StudentIdentity, StudentEligibility, GuardianRecipientContext, StaffIdentity, OrganizationContext and AcademicYearReference contracts. Authentication/RBAC/Audit remain Shared Platform contracts consumed by all domains.

### `GuardianUserAccountLinkContract` — FUTURE
Provider: Parent Portal / Shared Platform bridge.

Maps an authenticated User account to one canonical Guardian identity with effective/suspension/verification state. It never substitutes for Student↔Guardian authorization.

### `ParentPortalArtifactContract` — FUTURE
Provider: source reporting/domain publication service; consumer: Parent Portal.

Exposes only an exact `PUBLISHED`, parent-approved artifact/version plus safe metadata/actions. Parent Portal must not reconstruct official reports from raw source transactions or mutate the source artifact.

### `ParentPortalAuthorizationContract` — FUTURE
Provider: Parent Portal authorization service consuming Shared Core + Shared Platform contracts.

Resolves `User → Guardian → Student → exact artifact/action` eligibility on every protected request. WhatsApp/deep-link possession is not authorization.

### `CommunicationAudienceContract` — FUTURE
Provider: Shared Communication consuming Shared Core Staff/Organization/Assignment context.

Resolves an approved audience definition into canonical recipients at a specific point in time. Provider-side group membership is not the authority. Consumers receive recipient resolution/audit information, not permission to rewrite Staff assignments.

### `InstitutionalCommunicationTriggerContract` — FUTURE
Providers: source domains / authorized institutional workflow; consumer: Shared Communication.

Carries a minimal approved trigger such as:
- Academic calendar non-teaching event;
- approved schedule change;
- upcoming class-session obligation;
- authorized manual institutional announcement intent.

Communication never becomes the source fact.

### `ActionRequestResponseContract` — FUTURE
Provider: Shared Communication; consumers: owning domain workflows.

Provides structured response facts for an exact request/recipient, including response code, timestamp, channel and lineage. A response may create an operational exception but cannot directly mutate the owning domain transaction.

### `TeacherAvailabilityConfirmationContract` — FUTURE
Providers/consumers: Academic ↔ Shared Communication.

Academic exposes a future teacher/session obligation. Shared Communication requests/records structured availability response. Academic consumes `CONFIRMED`, `UNAVAILABLE`, or pending/no-response state only as readiness context. These states are never official teacher attendance and never auto-apply substitution/reschedule/cancellation.
