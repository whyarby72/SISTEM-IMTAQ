# Change Manifest — FIX-ACADEMIC-DASHBOARD-FILTER-LAYOUT

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Merapikan tata letak filter dashboard Waka Akademik agar tidak terpecah pada ukuran tablet dan tetap responsif pada mobile.

## Perubahan

- Mengganti layout filter dari flex wrapping ke grid.
- Desktop memakai kolom bulan, dua tanggal, tombol, dan ringkasan periode.
- Tablet menempatkan bulan di baris atas lalu tanggal dan tombol sejajar.
- Mobile memakai satu kolom penuh.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 108 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT: screenshot dashboard tablet menunjukkan tanggal mulai, tanggal selesai, dan tombol Terapkan sejajar.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan aturan CSS grid filter.

Status: DONE
