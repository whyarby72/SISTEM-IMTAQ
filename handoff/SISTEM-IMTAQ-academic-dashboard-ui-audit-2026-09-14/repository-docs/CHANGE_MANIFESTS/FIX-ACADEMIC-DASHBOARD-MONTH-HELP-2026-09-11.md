# Change Manifest — FIX-ACADEMIC-DASHBOARD-MONTH-HELP

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Menjelaskan mode filter dashboard agar pengguna memahami bahwa pilihan bulan memakai satu bulan penuh, sedangkan opsi kosong memakai rentang tanggal manual.

## Perubahan

- Menambahkan petunjuk visible di bawah dropdown bulan.
- Menambahkan `aria-describedby` untuk menghubungkan petunjuk dengan kontrol bulan.
- Tidak mengubah value query atau resolver periode.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 107 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard Juli: accessibility tree menampilkan petunjuk mode filter.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup menghapus petunjuk dan atribut ARIA pada view serta assertion test.

Status: DONE
