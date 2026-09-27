# Parent Portal UAT Matrix v1.0

Status: future acceptance contract. P0/P1 tests become mandatory before production activation.

| ID | Scenario | Expected | Pri |
|---|---|---|---|
| PORTAL-UAT-001 | Guardian user account linked to canonical Guardian | one active verified link | P0 |
| PORTAL-UAT-002 | user with no Guardian link logs in | no child/report access | P0 |
| PORTAL-UAT-003 | Guardian has two authorized children | both children listed, independently authorized | P1 |
| PORTAL-UAT-004 | Guardian authorized only for Student A tries Student B URL | DENY backend | P0 |
| PORTAL-UAT-005 | two Guardians for same Student | access follows each relationship/policy | P0 |
| PORTAL-UAT-006 | relationship ended | access follows explicit historical-access policy; never silently permanent | P0 |
| PORTAL-UAT-007 | report DRAFT | not visible | P0 |
| PORTAL-UAT-008 | report REVIEWED but not PUBLISHED | not visible | P0 |
| PORTAL-UAT-009 | PUBLISHED but not parent-approved | not visible | P0 |
| PORTAL-UAT-010 | exact approved report v1 | visible to authorized Guardian | P0 |
| PORTAL-UAT-011 | source corrected and report v2 published | v1 content remains immutable; current view resolves policy correctly | P0 |
| PORTAL-UAT-012 | change report UUID/path to another Student | DENY | P0 |
| PORTAL-UAT-013 | WhatsApp deep link opened while logged out | authenticate then re-authorize | P0 |
| PORTAL-UAT-014 | leaked deep link used by unrelated account | DENY | P0 |
| PORTAL-UAT-015 | expired signed/deep link | no content disclosure | P0 |
| PORTAL-UAT-016 | download without download permission | DENY while view may remain allowed | P0 |
| PORTAL-UAT-017 | account suspended during active session | subsequent access denied/session invalidated | P0 |
| PORTAL-UAT-018 | internal alert/note exists for Student | not serialized into Portal | P0 |
| PORTAL-UAT-019 | Guardian contact changes | historical report/access audit not rewritten | P1 |
| PORTAL-UAT-020 | repeated login failures | rate/security control + logging | P0 |
| PORTAL-UAT-021 | artifact view | access event recorded without report-body copy | P1 |
| PORTAL-UAT-022 | Portal unavailable | Academic transactions/report publication still operate | P0 |
| PORTAL-UAT-023 | one Guardian with two children downloads one report | no cross-child data in artifact | P0 |
| PORTAL-UAT-024 | user guesses NISN/student code in route/query | does not grant access | P0 |
| PORTAL-UAT-025 | parent AI disabled | normal Portal functionality unaffected | P1 |
