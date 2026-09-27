# Change Manifest — Indonesian weekday labels

- Request: nama hari pada daftar temuan kehadiran ditampilkan dalam bahasa Indonesia saja.
- Files changed: `application/web/resources/views/academic/attendance/exceptions.blade.php`.
- Implementation: moved the weekday map before its first template use and standardized Sunday to `Minggu`.
- Application behavior: presentation-only; no business behavior change.
- Database/migration/seed/import/RBAC/schedule/historical data: unchanged.
- Verification: Blade cache PASS; `AttendanceExceptionUiTest` 7 tests / 32 assertions PASS; browser AX verification showed `Kamis`, `Rabu`, `Selasa`, `Senin`, and `Minggu`, with no English weekday labels.
- Rollback: revert this view-only change and its documentation records.
- Status: COMPLETE.
