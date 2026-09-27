# Parent Portal Observability & Audit v1.0

## 1. Why read-access observability matters
Parent Portal is primarily read-only, so mutation audit alone is insufficient. Sensitive access needs security/access events without turning every page view into uncontrolled logs of report content.

## 2. Conceptual access-event store
`parent_portal_access_events`
- `id`
- `user_id`
- `guardian_id nullable`
- `student_id nullable`
- `artifact_type nullable`
- `artifact_version_id nullable`
- `event_type`
- `result`: `SUCCESS | DENIED | FAILED`
- `reason_code nullable`
- `occurred_at`
- minimal technical metadata / correlation id

Do not copy report body/content into access logs.

## 3. Event types
Candidate values:
- `LOGIN_SUCCESS`
- `LOGIN_FAILED`
- `ACCOUNT_ACTIVATED`
- `ACCOUNT_SUSPENDED`
- `AUTHORIZED_CHILDREN_VIEWED`
- `ARTIFACT_LIST_VIEWED`
- `ARTIFACT_VIEWED`
- `ARTIFACT_DOWNLOADED`
- `ACCESS_DENIED`
- `LOGOUT`

## 4. Security signals
Repeated failed login, repeated cross-Student access denial, impossible/abnormal session patterns or token abuse may generate security alerts. Thresholds and retention are policy/configuration, not hard-coded business truth.

## 5. Operational metrics
Useful service metrics:
- account activation success/failure;
- active portal accounts;
- report view success rate;
- download failures;
- authorization denial rate;
- portal/API latency/error rate.

Do not turn “parent did not open report” into a punitive student/guardian score.

## 6. Correlation
When a WhatsApp delivery points to a Portal artifact, delivery logs and Portal access logs may be correlated by safe message/artifact references. Delivery status and report access remain separate facts.
