# ACADEMIC-WEB-GRADE-WORKFLOW-G1-AUTHORIZATION-READ-SURFACE

## Task contract

- Type: full-stack read-only Academic grade workspace.
- Starting baseline: `64f9123dec48ff0a36d6a0b648c4e81164f34909`.
- No grade writes, migrations, pilot seeding, or database business-data writes.

## Required behavior

- Register `academic.grades` in the existing feature registry and fail closed for this route when the registry row is missing or disabled.
- Expose only `GET /academic/grades` (`academic.grades.index`).
- Enforce exact resource scope: active UserStaffLink plus active TeachingAssignment for subject teachers; effective WALI role/staff link/homeroom for Wali; WAKA_AKADEMIK role for Waka. Super Admin alone is not grade authority.
- Render semester/class/subject selectors and existing grade facts only. Missing grades must remain distinct from score zero.
- Add the sidebar entry only when the strict feature gate and at least one authorized scope are both true.
- Keep G2 DRAFT-only write hardening deferred.

## Forbidden

No POST/PUT/PATCH/DELETE grade endpoint, grade mutation, check/lock/correction action, migration, schema/config change, AI/provider change, or pilot/staging/production data mutation.

## Validation

Run focused grade/auth tests, Academic regression, Composer validation, PHP lint, view cache, Pint, project-structure check, diff check, and exact GitHub Actions foundation evidence when pushed.

## Routing after completion

On full pass: `GRADE_G1_IMPLEMENTED_PASS`; next task is `ACADEMIC-WEB-GRADE-WORKFLOW-G2-DRAFT-ENTRY-STATE-HARDENING`. Do not start G2 in G1.
