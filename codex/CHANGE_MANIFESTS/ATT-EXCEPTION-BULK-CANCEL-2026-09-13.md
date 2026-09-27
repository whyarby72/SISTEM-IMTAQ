# Change Manifest — Bulk cancellation preview usability

- Change ID: `ATT-EXCEPTION-BULK-CANCEL-2026-09-13`
- Scope: preserve exception-page month/class focus filters as initial values for the separate bulk-cancellation preview form.
- Application behavior: UI/form prefill only; the user must still click `Pratinjau` and confirm cancellation.
- Business rules: unchanged. Candidate filtering, authority checks, validation, transaction locking, and cancellation eligibility remain unchanged.
- Database migration/schema/data write: none introduced by this change.

## Files

- `application/web/app/Http/Controllers/Academic/AttendanceExceptionController.php`
- `application/web/resources/views/academic/attendance/exceptions.blade.php`
- `application/web/tests/Feature/Academic/AttendanceExceptionUiTest.php`

## Verification

- `AttendanceExceptionUiTest`: 8 tests, 36 assertions — PASS.
- `php artisan view:cache`: PASS.
- PHP syntax lint for changed PHP files: PASS.
