# Change Manifest — FIX-ACADEMIC-DASHBOARD-TEACHER-SCOPE-LABEL

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Menjelaskan bahwa rincian KPI kehadiran guru menghitung partisipasi penugasan sesi, bukan jumlah individu guru.

## Perubahan

- Label rincian KPI diubah dari `guru tercatat` menjadi `penugasan guru tercatat`.
- Perhitungan `resolved_participations`, `eligible_participations`, dan `absent` tidak diubah.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 103 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard Juli: accessibility tree menampilkan `penugasan guru tercatat`.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan perubahan label pada view dan assertion test.

Status: DONE
