# Change Manifest — FIX-ACADEMIC-DASHBOARD-SESSION-LABEL

Tanggal: 2026-09-11  
Modul: Academic / Dashboard Waka Akademik  
Change class: `MODULE_INTERNAL`

## Outcome

Memastikan label persentase sesi disahkan tidak terbaca sebagai persentase kehadiran santri.

## Perubahan

- Panel `Status Kehadiran Periode Terpilih` menjadi `Status Sesi Periode Terpilih`.
- Label `Kehadiran santri` menjadi `Pengesahan sesi`.
- Angka, query, sumber transaksi, database, migration, dan RBAC tidak berubah.

## File

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AcademicRoleDashboardServiceTest` dan `TeacherAttendanceServiceTest`: 29 test lulus, 102 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT dashboard Juli: istilah baru tampil pada accessibility tree; nilai pengesahan tetap 12.58%.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan dua perubahan view/test pada commit perubahan terkait.

Status: DONE
