# Change Manifest — SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1R

Date: 2026-10-02  
Branch: `feat/super-admin-user-access-preferences`  
Starting HEAD: `79fb525c4369f7d31530a2e5f261499713e37ddd`

## Scope

Security and functional completion of the existing Super Admin User & Akses
surface. Application source, one new password-lifecycle migration, views,
focused tests, and governance metadata only.

## Changed files

- `application/web/app/Http/Controllers/Admin/UserAccessController.php`
- `application/web/app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `application/web/app/Http/Middleware/EnsureFeatureAccess.php`
- `application/web/app/Http/Middleware/EnsureActiveAccount.php`
- `application/web/app/Http/Services/RoleScopeValidator.php`
- `application/web/app/Models/User.php`
- `application/web/bootstrap/app.php`
- `application/web/database/migrations/2026_10_02_000003_add_must_change_password_to_users_table.php`
- `application/web/routes/web.php`
- User & Akses and password-change Blade views
- `application/web/tests/Feature/Admin/UserAccessManagementTest.php`
- V1R task/manifest and state evidence

## Safety boundary

- PILOT/staging/production database write: `NONE`.
- Local database migration/test write: `NONE`; disposable identity was not
  proven because `imtaq_test_v1r` and `imtaq_ci_test` were unavailable.
- Applied migrations were not edited.
- Academic business data, attendance semantics, AI/provider state, credentials,
  Public Academic AI, and UAT state were untouched.
- No secret, password, hash, token, or temporary credential is rendered or
  written to audit payloads.

## Validation evidence

- `php -l` changed PHP: PASS.
- Blade `view:cache`: PASS.
- `route:list`: PASS.
- Pint on changed PHP/test files: PASS.
- `scripts/check_project_structure.py`: PASS.
- `git diff --check`: PASS.
- Focused PHPUnit: `NOT RUN`; disposable PostgreSQL target unavailable and
  guard refused pilot `.env`.
- Exact GitHub Actions: pending; this checkpoint is not PASS.

## Rollback

Revert the V1R application/evidence commit. If later replayed, reset only the
disposable PostgreSQL database. Do not roll back applied pilot history.

## Decision

`HOLD / FEATURE_GATE_SECURITY_INCOMPLETE`

## Next atomic task

Provision and independently prove a disposable PostgreSQL target, then rerun
focused User & Akses, migration-from-zero, Academic/AI regression, and exact
foundation CI. Do not migrate PILOT or start Academic UAT.
