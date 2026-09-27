# Change Manifest — FIX-ACADEMIC-DASHBOARD-APPLY-BUTTON-ALIGNMENT

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Menyamakan posisi vertikal tombol Terapkan dengan input tanggal berdasarkan feedback visual browser.

## Perubahan

- Menyesuaikan offset atas tombol agar garis atas dan bawah tombol sejajar dengan kontrol tanggal.
- Tidak mengubah ukuran, interaksi, submit behavior, atau layout mobile.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 108 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT: screenshot viewport lebar menunjukkan tombol Terapkan sejajar dengan input tanggal.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan offset CSS tombol filter.

Status: DONE
