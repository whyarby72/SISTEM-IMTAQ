# Change Manifest — FIX-ACADEMIC-DASHBOARD-ARIA-CLASS-SCOPE

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Memastikan pembaca layar dapat menghubungkan setiap progress bar kelengkapan dengan kelas yang benar.

## Perubahan

- Label `aria-label` pada progress bar kelas kini menyertakan nama kelas.
- Nilai dan tampilan visual tidak berubah.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 106 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard default: accessibility tree menampilkan label spesifik seperti `Kelengkapan kehadiran Kelas 1`.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan label ARIA pada view dan assertion test.

Status: DONE
