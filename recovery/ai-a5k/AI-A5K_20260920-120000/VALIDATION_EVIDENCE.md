# AI-A5K Validation Evidence

- Focused AI-A5K: 33 tests / 113 assertions / 0 failures.
- Academic: 364 tests / 1,456 assertions / 0 failures.
- Full: 477 tests / 1,924 assertions / 0 failures.
- View cache, PHP lint, and Pint: PASS.
- Route inventory: Super Admin provider routes exist under `admin/system/ai-provider`; all require `auth`, controller enforces institution-wide authority.
- Pilot migration status: 42 `Ran`, AI-A5K migration `Pending`; no pilot migration was applied.
- PostgreSQL `migrate --pretend`: PASS.
- Fresh custom PostgreSQL backup: PASS; `pg_restore --list`: PASS.
- Backup evidence hash: `4e88818468cc329d8d112dd28ebd97a6d875d9308fccf4bb7341df636b681461`.
- `pg_restore --list` evidence hash: `cb9e36fd164ac5744b78687b616348a7b35a628d09ed0052d9a5e03a47d62040`.
- Disposable PostgreSQL create/apply/drop: NOT RUN — execution approval runner rejected the operation; this is the remaining gate.
- No OpenAI live request, credential seeding, public AI activation, academic business write, attendance write, occurrence write, or grade write.
