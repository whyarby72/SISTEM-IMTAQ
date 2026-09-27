# Change Manifest — FIX-ACADEMIC-DASHBOARD-APPLY-BUTTON

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Merapikan tombol Terapkan sebagai aksi utama filter dashboard agar lebih seimbang, mudah dikenali, dan tetap usable pada keyboard/mobile.

## Perubahan

- Menambahkan ukuran minimum dan radius tombol yang konsisten.
- Menambahkan shadow serta state hover, active, dan focus-visible.
- Menjaga alignment desktop/tablet dan full-width mobile.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 108 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT: screenshot menunjukkan tombol Terapkan rapi dan sejajar dengan kontrol tanggal.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan aturan CSS tombol filter.

Status: DONE
