# AI-A2 Validation Evidence

- AI-A2 focused tests: 18 tests / 57 assertions — PASS
- Academic regression: 347 tests / 1384 assertions — PASS
- Full application suite: 460 tests / 1852 assertions — PASS
- PHP lint: PASS
- Pint on changed AI-A2 files: PASS
- Blade view cache: PASS
- PostgreSQL migration status: 42 migration files / 42 rows, all Ran, 0 Pending — read-only
- Pilot PostgreSQL test writes: NO
- Live OpenAI smoke: NOT_RUN (deterministic provider fake used; no credential was exposed or required)

Security/failure coverage includes strict five-tool schemas, absence of built-in tools in the request construction, server-side key transport, store=false, automatic tool choice, disabled parallel calls, call-id preservation, multi-round calls, unknown tool rejection, malformed arguments, ambiguous identity continuation blocking, bounded loop, provider failure, and audit question hashing without question persistence.

