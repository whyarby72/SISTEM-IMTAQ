# Change Manifest — FIX-ACADEMIC-DASHBOARD-ARIA-PROGRESS

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Memastikan indikator kelengkapan kelas dan tren kehadiran dapat dipahami melalui pembaca layar, termasuk pembedaan antara nilai nol dan data yang belum tersedia.

## Perubahan

- Menambahkan `role="progressbar"`, `aria-valuemin`, `aria-valuemax`, dan `aria-valuenow` pada indikator dengan nilai.
- Menambahkan `aria-valuetext` pada indikator yang belum memiliki data.
- Tidak mengubah nilai, query, sumber transaksi, database, migration, atau RBAC.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 105 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard Juli: accessibility tree menampilkan progress indicator kelas dan tren beserta nilai atau status belum tersedia.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup menghapus atribut ARIA pada view dan assertion terkait.

Status: DONE
