# Change Manifest — FIX-ACADEMIC-DASHBOARD-FILTER-ALIGNMENT

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Merapikan perataan visual kontrol filter dashboard berdasarkan umpan balik browser.

## Perubahan

- Mengubah alignment filter agar label bulan dan tanggal dimulai pada garis atas yang sama.
- Menjajarkan tombol Terapkan dengan input tanggal pada desktop/tablet.
- Menjaga ringkasan periode sebagai baris terpisah dan layout satu kolom pada mobile.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 108 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT: screenshot menunjukkan kontrol filter sejajar dan ringkasan periode tidak menempel.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan aturan CSS alignment filter.

Status: DONE
