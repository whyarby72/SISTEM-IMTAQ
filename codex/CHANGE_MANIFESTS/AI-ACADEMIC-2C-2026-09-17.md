# Change Manifest — AI Academic Assistant Phase 2C

Date: 2026-09-17

## Changed

- Canonical LATE/EXCUSED metric semantics and official numerator.
- Canonical completed-session denominator; historical PLANNED work queue is excluded from official denominator.
- Explicit, permission-gated attendance source certification/revocation service with audit trail.
- `academic.attendance.source_certify` permission definition in the existing Academic role seeder.
- Repository-local July validator fixture replacing an absolute Downloads path.
- July 2026 read-only certification/readiness dossier.

## Not changed

- No real database migration, seed, import, certification, or historical rewrite.
- No AI endpoint, LLM call, chat UI, or AI write capability.
- No legacy monthly table or snapshot mutation.

## Migration result

The Phase 2B semantic foundation migration was exercised by RefreshDatabase tests. It was not executed against the pilot PostgreSQL database. The pilot database therefore has no new certification/lineage tables yet.

## Evidence

See `codex/AI_ACADEMIC_PHASE_2C_JULY_2026_CERTIFICATION_DOSSIER.md` and the Phase 2C focused tests.
