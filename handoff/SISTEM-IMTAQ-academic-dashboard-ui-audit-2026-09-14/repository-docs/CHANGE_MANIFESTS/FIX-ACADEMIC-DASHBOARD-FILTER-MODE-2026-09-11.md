# Change Manifest — FIX-ACADEMIC-DASHBOARD-FILTER-MODE

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Memberi status eksplisit tentang mode filter yang sedang aktif pada dashboard Waka Akademik.

## Perubahan

- Menambahkan label `Mode bulan penuh` saat parameter bulan dipakai.
- Menambahkan label `Mode rentang manual` saat pengguna memakai tanggal mulai/sampai.
- Tidak mengubah resolver, query, value filter, atau sumber data.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 108 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard Juli: mode aktif tampil eksplisit sebagai `Mode bulan penuh`.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup menghapus label mode pada view dan assertion test.

Status: DONE
