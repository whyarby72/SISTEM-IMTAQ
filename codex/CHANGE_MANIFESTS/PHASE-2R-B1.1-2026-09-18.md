# Change Manifest — PHASE 2R-B1.1

Date: 2026-09-18  
Scope: Canonical reconciliation accounting and metric invariant hardening

## Preconditions

- CR-S.1 durable baseline verified before change.
- CR-S.1 checksum verification: 4/4 PASS.
- Pre-B1.1 source manifest: 380 files.
- Test database: SQLite `:memory:`.

## Files changed

- `application/web/app/Domains/Academic/Services/CanonicalAttendanceSemanticService.php`
  - Exposes `accounting_invariant` containing eligible, resolved, missing, and reconciliation-required counts.
  - Proves the transitional accounting relationship at the canonical metric boundary.
- `application/web/tests/Feature/Academic/CanonicalAttendanceSemanticContractTest.php`
  - Adds official-rate, reconciliation-plus-missing, zero-denominator, and source-authority compatibility coverage.

## Resolver audit

The Phase 2R-B1 changes in `AttendanceSourceAuthorityResolver.php` were compared with the CR-S durable baseline and classified as:

`SEMANTIC_ADAPTER_COMPATIBILITY` / `COMPATIBILITY_ONLY`

They propagate `semantic_decision_required` and use canonical PRESENT/ELIGIBLE for the rate. No source precedence, certification, conflict, or authority-selection policy was changed.

## Explicit non-changes

- No dashboard, export, or legacy metrics consumer migration.
- No database, migration, seed/import, RBAC, lineage, UI, historical rewrite, or AI/OpenAI change.

## Verification

- Focused B1.1 tests: 17 tests / 93 assertions — PASS.
- Academic regression: 290 tests / 1,241 assertions — PASS.
- Full suite: 403 tests / 1,709 assertions — PASS.
- PHP lint — PASS.
- Pint — PASS.
- Post-change source comparison: 2 intended modified files, 0 added, 0 removed, 0 unexpected.
