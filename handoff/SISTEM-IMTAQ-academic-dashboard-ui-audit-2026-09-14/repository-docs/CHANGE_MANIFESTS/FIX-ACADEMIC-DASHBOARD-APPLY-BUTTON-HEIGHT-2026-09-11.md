# Change Manifest — FIX-ACADEMIC-DASHBOARD-APPLY-BUTTON-HEIGHT

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Menyamakan tinggi visual tombol Terapkan dengan input tanggal pada filter dashboard.

## Perubahan

- Menetapkan tinggi tombol secara eksplisit.
- Menjaga teks tetap terpusat dan state hover/active/focus tetap tersedia.
- Layout mobile tetap full-width.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 108 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT: screenshot menunjukkan tombol dan input tanggal lebih sejajar.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan tinggi CSS tombol filter.

Status: DONE
