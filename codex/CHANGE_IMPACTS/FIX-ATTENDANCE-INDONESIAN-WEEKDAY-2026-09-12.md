# Change Impact Record — Indonesian weekday labels

- Request: tampilkan nama hari pada daftar temuan kehadiran dalam bahasa Indonesia.
- Date: 2026-09-12
- Change class: `APPLICATION_FEATURE`
- Scope: `application/web/resources/views/academic/attendance/exceptions.blade.php`.
- Behavior: weekday labels in the exception table and bulk date helper use Indonesian names, including `Minggu`.
- Business logic, routes, filters, authorization, persistence, and date values are unchanged.
- Write scope: exception attendance view, focused regression evidence, and work log.
- Status: safe checkpoint; browser preview verified.
