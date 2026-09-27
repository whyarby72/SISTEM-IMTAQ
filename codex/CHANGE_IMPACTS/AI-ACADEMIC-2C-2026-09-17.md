# Change Impact — AI Academic Assistant Phase 2C

## Scope

Canonical attendance semantic calculations, historical planned-session handling, fixture portability, source certification workflow, July read-only dossier, and the certification permission convention.

## Expected writes

- Code, tests, documentation, and a permission-seeder definition only.
- No migration execution against the pilot database.
- No seed/import execution, no attendance rewrite, and no source certification row.

## Protected behavior

Existing attendance forms/routes, raw attendance rows, legacy monthly tables, and database data remain unchanged. Certification is never performed by an LLM.

## Validation gate

Focused semantic/certification/fixture tests, Academic regression, Shared regression, view cache, PHP lint, and Pint.
