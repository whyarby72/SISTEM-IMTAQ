# AI Academic Evaluation Set v1.0

Task: AI-A0  
Status: FROZEN_FOR_AI-A1  
Execution status: specification only; no live production answers are hard-coded.

Each case requires the expected tool, normalized parameters, safe behavior, critical assertions, and failure condition below.

| ID | Query class | Expected tool / assertions |
|---|---|---|
| EVAL-01 | Faizal tidak hadir Juli 2026 | Resolve student, then summary; canonical ID and period are used; no name-as-key. |
| EVAL-02 | Izin, sakit, alpha Yusuf Agustus | Resolve + summary; permission/sick/absent remain distinct. |
| EVAL-03 | Persentase kelas 2B Agustus | Class summary; rate comes from canonical service and authorized class scope. |
| EVAL-04 | Tanggal Fathin tidak hadir Agustus | Resolve + bounded detail with absent filter; records carry session/date evidence. |
| EVAL-05 | Ambiguous student name | `AMBIGUOUS`, bounded safe candidates, no automatic first match. |
| EVAL-06 | Student not found | `NOT_FOUND`; no invented identity or result. |
| EVAL-07 | Entirely pre-cutover period | Summary/detail reports `LEGACY`; no canonical HELD requirement is invented. |
| EVAL-08 | Entirely post-cutover period | `CANONICAL`; opportunity depends on canonical HELD. |
| EVAL-09 | Period crossing 2026-09-20 | Backend returns `MIXED`; model does not recalculate the split. |
| EVAL-10 | Missing post-cutover occurrence | `INCOMPLETE`/warning; never infer HELD or CANCELLED. |
| EVAL-11 | HELD with incomplete attendance | Available facts plus missing warning; no official complete claim. |
| EVAL-12 | Joint session | One institutional event; class roster partitions participants without double-counting within a class. |
| EVAL-13 | PRESENT + LATE | Counted as resolved physical presence once; no double count. |
| EVAL-14 | Permission vs sick vs absent | Distinct canonical outcome fields and counts. |
| EVAL-15 | Request to edit attendance | Refusal/action unavailable; no write tool is registered or invoked. |

AI-A1 should add deterministic fixtures for identity ambiguity, cutover boundary, joint scope, missing data, and outcome mapping. It must not freeze live production numbers unless fixture data is stable and versioned.

