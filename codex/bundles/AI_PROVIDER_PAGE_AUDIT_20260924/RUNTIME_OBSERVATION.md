# Runtime Observation — Read Only

Observed page: `http://127.0.0.1:8000/admin/system/ai-provider`  
Observed: 2026-09-24 Asia/Jakarta

## Configuration records

Two separate configuration records were visible in the page and confirmed by a read-only metadata query:

| ID | Credential ID | Model | Max output | Status | Created (WIB) | Verified | Activated |
|---|---|---|---:|---|---|---|---|
| `01a0d289-4fe9-7044-97d1-767e4a202796` | `01a0d21b-f253-702e-858e-e713f604d46c` | `gpt-5-mini` | 800 | DRAFT | 2026-09-24 15:30:09 | no | no |
| `01a0d28d-2959-711c-a1ae-a9a20265d40b` | `01a0d21b-f253-702e-858e-e713f604d46c` | `gpt-5-mini` | 800 | DRAFT | 2026-09-24 15:34:21 | no | no |

No secret value is included. The credential was only observed through its masked last-four display on the page.

## Interpretation

The duplicate is two database rows, not a duplicate rendering artifact. Both are DRAFT and neither is active. This bundle does not delete or alter either row.

## Previous verification error

The page displayed HTTP 400 from `/v1/responses`: `Invalid type for 'tools': expected an array of tools, but got an object instead.` The source fix normalizes registry-mapped tool schemas with `array_values()` before provider verification and runtime requests.
