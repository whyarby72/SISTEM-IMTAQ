# July 2026 Student Identity Mapping Review

## Scope

- Source period: `2026-07`
- Staging roster: `staging_imtaq_students_july_2026`
- Staging attendance: `staging_imtaq_attendance_july_2026`
- Required mapping: `source_record_id` → permanent `students.id`
- Source identifiers such as `JUL26-xxx` are staging references only.

## Current result

| Class_Admin | Roster rows | Exact canonical-name matches | Mapping status |
|---|---:|---:|---|
| 1 | 20 | 0 | REVIEW_REQUIRED |
| 2A | 19 | 0 | REVIEW_REQUIRED |
| 2B | 10 | 0 | REVIEW_REQUIRED |
| 3A | 15 | 0 | REVIEW_REQUIRED |
| 3B | 20 | 0 | REVIEW_REQUIRED |
| **Total** | **84** | **0** | **84 UNMAPPED** |

## Identity findings

- Canonical `students` rows currently present: 10.
- Current canonical rows are sample records; none exactly matches the 84 historical Indonesian names.
- No source row includes a permanent Student_ID.
- No automatic fuzzy/name/sequence mapping is approved.
- No canonical student rows, enrollments, or production attendance facts were created by this review.

## Required decision before production migration

Provide or verify a canonical student roster containing permanent Student_IDs and a trusted matching key (institutional student code/NISN/NIS as applicable). Then review every candidate, resolve duplicates/ambiguous matches, and only accept the batch when mapping is 84/84 and lineage is recorded.

## Gate

`PRODUCTION_MIGRATION_ALLOWED = NO`

Reason: `84` source rows remain unmapped to permanent canonical `students.id`.
