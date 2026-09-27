# Change Manifest — FIX-ACADEMIC-DASHBOARD-TEACHER-KPI-LABEL

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Scope: Perubahan internal modul; tidak mengubah database, migration, RBAC, atau transaksi bisnis.

## Perubahan

- Mengganti label fallback KPI kehadiran guru dari “Belum ada sesi selesai” menjadi “Belum ada data kehadiran guru” ketika sesi akademik tersedia tetapi data guru belum tercatat.
- Menambahkan assertion regresi pada feature test dashboard agar label tetap semantik dan tidak mengaburkan status sesi santri.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 100 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard Juli: accessibility tree menampilkan `Belum ada data kehadiran guru`, sementara ringkasan sesi santri tetap menampilkan sesi disahkan/belum disahkan.

## Risiko dan tindak lanjut

- Tidak ada perubahan skema atau data persisten.
- Lanjutkan ke checkpoint berikutnya setelah ada prioritas audit/perbaikan dashboard Waka Akademik yang baru.
