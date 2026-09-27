# Change Manifest — FIX-ACADEMIC-DASHBOARD-MONTH-LABEL

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Membuat pilihan bulan lebih natural dengan menghilangkan tanggal `01` yang tidak bermakna bagi filter bulanan.

## Perubahan

- Label opsi dropdown memakai format `Nama bulan Tahun`, misalnya `Juli 2026`.
- Value opsi tetap memakai format `YYYY-MM`; resolver periode tidak berubah.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 106 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard Juli: dropdown menampilkan `Juli 2026`, `Agustus 2026`, dan seterusnya tanpa awalan tanggal.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan formatter label dropdown dan assertion test.

Status: DONE
