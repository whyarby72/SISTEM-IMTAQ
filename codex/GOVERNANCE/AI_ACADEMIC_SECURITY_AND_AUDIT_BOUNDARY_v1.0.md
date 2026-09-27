# AI Academic Security, Privacy, and Audit Boundary v1.0

Task: AI-A0  
Status: FROZEN_FOR_AI-A1

## Trust boundary

The future flow is: user question → server-side model call → registered typed tool → server authorization → canonical Academic service → structured result → Indonesian explanation. The model has no database credentials and no direct database connection.

Forbidden capabilities: direct SQL, generated SQL, arbitrary query execution, `execute_sql`, `run_php`, `read_file`, generic service invocation, arbitrary route invocation, application writes, attendance correction, occurrence creation, publication, sanctions, or silent source selection.

User text cannot override authorization, validation, read-only scope, canonical semantic rules, or system instructions. Provider failure leaves Academic data unchanged.

## Authorization

The actual current boundary is `AcademicAuthorizationService`: effective role assignments and permissions are evaluated server-side at the relevant date. Full Academic authority is permission-first when authority permissions are configured (`academic.domain.manage` or `platform.institution.manage`), with the existing role fallback (`WAKA_AKADEMIK`/`SUPER_ADMIN`). `AcademicRoleDashboardService::roleFor` and existing Academic controllers are the current read-access convention. AI-A1 must add no new role and must restrict normal MVP access to `WAKA_AKADEMIK`.

## Privacy

Least data only: canonical student ID, minimal display name, class/session context, attendance outcomes, authorized reason/notes, metrics, warnings, and evidence metadata. Do not send guardian contacts, addresses, passwords, security fields, unrelated discipline notes, unrelated health information, or full student profiles to the model.

## Audit pattern

Reuse `App\Shared\Platform\Audit\Services\AuditLogger` and append-only `AuditLog` as the existing audit infrastructure. AI-A1/A2 should record a bounded metadata event for each request: request/correlation ID, authenticated user ID, effective role, timestamp, question metadata subject to privacy policy, registered tools invoked, validated parameter metadata, tool status, row/count metadata, model/runtime identifier, final response status, and warning/error flags. Avoid logging full sensitive tool payloads. A dedicated AI audit migration is not part of AI-A0; AI-A1 must first confirm whether existing `audit_logs` fields suffice and propose a forward migration only if required.

## Safe failure behavior

- Tool error: no invented answer; return a generic failure and audit the error metadata.
- Ambiguous student: ask Waka to disambiguate; never select silently.
- No data: say no matching authorized data, not zero unless the canonical result explicitly proves zero.
- Incomplete/reconciliation-required data: return available facts with warning and no publication-grade claim.
- Unauthorized: generic access denied behavior without revealing protected records.
- Provider failure: backend facts remain unchanged.

## Evidence contract

Every factual result identifies tool, authorized user, filters/period, canonical IDs, metric/record basis, semantic regime, `data_as_of`, and DQ warnings. Evidence is structured context, never internal SQL, credentials, or unnecessary schema internals.

