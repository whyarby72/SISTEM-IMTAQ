# Change Manifest — FIX-ACADEMIC-EXCEPTIONS-BULK-ACTION-ORDER

Tanggal: 2026-09-11  
Modul: Academic / Attendance Exceptions  
Change class: `MODULE_INTERNAL`

## Outcome

Menempatkan tindakan Pembatalan Sesi Massal setelah pengguna melihat Fokus daftar temuan dan tabel sesi.

## Perubahan

- Memindahkan blok Pembatalan Sesi Massal ke bawah card Fokus daftar temuan.
- Tidak mengubah route, validation, CSRF, RBAC, query, atau data.

## File

- `application/web/resources/views/academic/attendance/exceptions.blade.php`
- `codex/WORK_LOG.md`

## Verifikasi

- `AttendanceExceptionUiTest` dan `StudentAttendanceUiTest`: 16 test lulus, 103 assertion.
- `php artisan view:cache`: lulus.
- Browser UAT: screenshot dan accessibility tree menunjukkan tabel temuan lebih dulu, Pembatalan Sesi Massal setelahnya.

## Dampak dan rollback

- Tidak ada perubahan database, kontrak publik, otorisasi, atau data historis.
- Rollback cukup mengembalikan urutan dua section di Blade.

Status: DONE
