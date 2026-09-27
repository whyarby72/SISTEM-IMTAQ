# QA Schedule v1.1 — 2026-09-07

## Result

- Automated targeted tests: 20 passed, 74 assertions.
- Official schedule sessions: 1,261.
- Session scopes: 1,570.
- Joint sessions: 309.
- Attendance rows changed by schedule import/generation: 0.

## Acceptance cases

1. PASS — 2026-08-05 Group B uses AKH and both periods.
2. PASS — 2026-08-12 Group B uses ADT.
3. PASS — 2026-08-19 Group B uses AKH.
4. PASS — 2026-08-26 Group B uses ADT.
5. PASS — 2026-09-30 week 5 uses ADT; no unresolved occurrence.
6. PASS — AKH [1,3], ADT [2,4,5].
7. PASS — sport excluded from Academic; no teacher double-booking created.
8. PASS — HAR has no Academic assignment.
9–11. PASS — 8, 17, 30 August are `CANCELLED_INSTITUTION`.
12. PASS — cancelled dates produce zero sessions.
13. PASS — no empty-slot rule was imported.
14. PASS — unrelated overlap checker tests pass; TG-B overlaps are represented as joint scope.
15. PASS — invalid time interval rejected.
16. PASS — publication validator blocks missing/inactive teacher.
17. PASS — publication validator blocks missing subject/group scope.
18. PASS — importer and generator reruns are idempotent.
19. PASS — importer increments version and appends audit revision records.
20. PASS — relation uses staff/subject codes and internal UUIDs, never teacher names.

## Gate

Official schedule publication completed: 51 rules and 51 assignments are `PUBLISHED`; 102 publication audit records were written. Roster snapshot and attendance remain separate.
