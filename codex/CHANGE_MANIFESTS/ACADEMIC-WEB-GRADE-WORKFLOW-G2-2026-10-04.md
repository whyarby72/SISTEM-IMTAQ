# Change Manifest — Academic Web Grade Workflow G2

Date: 2026-10-04
Task: `ACADEMIC-WEB-GRADE-WORKFLOW-G2-DRAFT-ENTRY-STATE-HARDENING`
Branch: `feat/super-admin-user-access-preferences`
Starting HEAD: `3083c51109922cf1f4814505177670ff8720e9f2`
Tested executable HEAD: `b3878d9f02fbbde06dc9e23b2562e8ad8eaacd88`
Exact CI: `37200924044` = `SUCCESS`

## Implemented contract

- `SemesterGradeEntryService::save()` now rejects normal mutation of CHECKED
  and LOCKED rows inside the locked transaction, requires the exact expected
  version for existing DRAFT rows, rejects versions on creation, and suppresses
  version/audit changes for canonical no-ops.
- `SemesterGradeDraftBatchService` owns one outer transaction, validates all
  enrolled students and versions before the first write, rejects ambiguous
  assignment provenance, and rolls the entire batch back on any invalid row.
- Write authority is the authenticated user's one effective Staff identity and
  one exact active/effective TeachingAssignment for semester, class, and
  subject. Wali, Waka, and Super Admin roles alone remain denied.
- Normal web entry always derives `DIRECT_ENTRY`, assignment ID, and responsible
  Staff ID server-side. Client provenance/state/audit fields are prohibited.
- Score `0` is retained as a real value; blank new rows remain missing without
  creating records; an existing DRAFT may be cleared to `NULL` with one version
  increment and audit; an already-null blank row is a no-op.
- The grade workspace provides keyboard-friendly numeric inputs and one
  explicit `Simpan Draft` batch action only for editable DRAFT/missing rows in
  exact teacher scope. Wali/Waka and CHECKED/LOCKED rows remain read-only.
- Exactly one write endpoint was added: POST
  `/academic/grades/batch-draft`, protected by auth, active-account, and strict
  `academic.grades` feature middleware plus domain authorization.

## Executable files

- `application/web/app/Domains/Academic/Services/SemesterGradeAuthorizationService.php`
- `application/web/app/Domains/Academic/Services/SemesterGradeEntryService.php`
- `application/web/app/Domains/Academic/Services/SemesterGradeDraftBatchService.php`
- `application/web/app/Domains/Academic/Services/SemesterGradeWorkspaceService.php`
- `application/web/app/Http/Controllers/Academic/SemesterGradeController.php`
- `application/web/resources/views/academic/grades/index.blade.php`
- `application/web/routes/web.php`
- `application/web/tests/Feature/Academic/SemesterGradeEntryServiceTest.php`
- `application/web/tests/Feature/Academic/SemesterGradeWorkspaceUiTest.php`
- `application/web/tests/Feature/Academic/SemesterGradeDraftWorkflowTest.php`

## Validation evidence

- Composer strict validation: PASS.
- PHP lint for changed PHP files: PASS.
- Pint: PASS.
- Blade compile plus compiled-template PHP lint: PASS.
- Route inspection: GET/HEAD grade workspace plus exactly one POST batch-DRAFT
  route with required middleware; no CHECK/LOCK/correction route.
- Project structure and `git diff --check`: PASS; canonical queue marker remains
  `SOC-MD-06`.
- Local database tests: correctly stopped by the fail-closed test guard because
  local `.env` resolves to protected PILOT `imtaq`; no bypass and no write.
- Disposable PostgreSQL 18.6 GitHub Actions: run `37200924044` passed migration
  replay, schema/identity assertions, all focused G2 regressions, and the full
  foundation suite: 15 passed, 558 warnings, 2359 assertions, 0 failed.
- G1 evidence correction: run `37194977772` is recorded as 15 passed, 544
  warnings, 2288 assertions, 0 failed; historical meaning was unchanged.

## Safety and rollback

No migration, schema/configuration/dependency change, PILOT database access or
write, PILOT `academic.grades` seed, AI/provider mutation, Public Academic AI
activation, G3 transition, report, or transcript work occurred. Rollback is a
Git revert of the G2 executable commits; no persistent data rollback is needed.

Academic Web completion remains `4/10 = 40% COMPLETE_EVIDENCED`; capability #5
remains PARTIAL until DRAFT, Wali CHECK, Waka LOCK, correction, and end-to-end
evidence are complete.

## Decision

`GRADE_G2_IMPLEMENTED_PASS`

Next: `RETURN_TO_CHATGPT_FOR_ACADEMIC_WEB_GRADE_G2_AUDIT`. Do not start G3.
