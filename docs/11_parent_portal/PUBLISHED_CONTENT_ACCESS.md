# Published Content Access v1.0

## 1. Parent Portal consumes artifacts, not raw transaction tables
Initial Academic flow:

`semester grades + locked attendance → Academic Report Card → reviewed/approved → PUBLISHED → parent-approved artifact → Parent Portal`

Portal must not reconstruct a report independently from raw grades/attendance.

## 2. `ParentPortalArtifactContract`
Provider: source reporting/domain service. Consumer: Parent Portal.

Minimum conceptual payload:
- `artifact_id`
- `artifact_type`
- `artifact_version_id`
- `artifact_version_no`
- `student_id`
- `title`
- `period_label`
- `published_at`
- `publication_status`
- `approved_for_parent_access`
- `classification`
- `rendering_reference` or safe rendering service contract
- `allowed_actions` such as `VIEW`, optional `DOWNLOAD`
- `superseded_by_version_id nullable`

It must not expose internal source rows merely because they contributed to the artifact.

## 3. Academic MVP content
Candidate first artifact:
- `ACADEMIC_REPORT_CARD`

Transcript parent availability remains `POLICY_PENDING` because transcript may have different document/authority rules.

## 4. Versioning
A Parent sees an exact published version. If source data is corrected and report v2 is published, report v1 remains immutable/auditable. Portal must never silently mutate v1 to display v2 values.

Default UI should emphasize the current official version. Whether superseded versions remain directly visible to Guardians is `POLICY_PENDING`.

## 5. Download
Viewing structured content and downloading PDF are separate actions. `DOWNLOAD` may be disabled while `VIEW` remains allowed. Download policy is `POLICY_PENDING`.

## 6. Parent-facing notes
Only notes/content explicitly approved for parent-facing publication enter the artifact. Internal Wali Kelas, Kesantrian, alert or coaching notes are excluded by default.

## 7. Future summary cards
Future Portal versions may display parent-approved summaries (attendance counts, Tahfizh progress, targets) through explicit parent-facing semantic contracts. These must never be inferred from unrestricted Student 360 or internal dashboards.
