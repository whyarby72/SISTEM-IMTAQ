# Change Manifest — FIX-ACADEMIC-TEACHER-ATTENDANCE

Tanggal: 2026-09-11  
Modul: Academic / Attendance  
Change class: `MODULE_CONTRACT`

## Outcome

Menyediakan pencatatan kehadiran guru per sesi untuk evaluasi performa tanpa menganggap pembatalan resmi pesantren sebagai ketidakhadiran guru.

## Perubahan

- Menambahkan endpoint dan form rekap kehadiran guru per participation.
- Mendukung status `PRESENT`, `ABSENT`, `SICK`, `IZIN`, dan `OTHER` dengan alasan wajib untuk selain `PRESENT`.
- Memperluas ringkasan dashboard Waka dengan rincian status guru.
- Menegaskan pada UI bahwa guru berhalangan tidak boleh diselesaikan dengan membatalkan sesi; gunakan pengganti/wali kelas agar santri tetap diabsen.
- Guru pengganti default adalah Wali Kelas aktif; catatan sesi mendukung keterangan aktivitas pengganti seperti Ngaji Auditorium dan pilihan guru lain tetap tersedia.
- Akses cepat “Catat kehadiran guru” ditampilkan setelah ringkasan santri dan melompat ke form rekap pada halaman yang sama, dengan layout mobile responsif.
- Pengesahan absensi santri kini mensyaratkan guru pengganti/Wali Kelas berstatus Hadir jika guru utama dicatat tidak hadir, sakit, izin, atau lainnya; kegagalan tidak mengubah status santri maupun sesi.
- Redirect setelah menyimpan status guru atau guru pengganti kembali ke panel `#rekap-guru`.
- Otorisasi rekap guru diselaraskan dengan RBAC: Waka Akademik dapat mencatat seluruh kelas dalam scope-nya, sedangkan Wali Kelas tetap terbatas pada kelas efektif.
- Memperbarui aturan bisnis/schema contract; tidak ada migration database dan tidak ada perubahan data historis.

## Files

- `application/web/app/Domains/Academic/Services/TeacherAttendanceService.php`
- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`
- `application/web/routes/web.php`
- `application/web/resources/views/academic/attendance/show.blade.php`
- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/TeacherAttendanceServiceTest.php`
- `application/web/app/Domains/Academic/Services/StudentAttendanceFinalizer.php`
- `application/web/tests/Feature/Academic/StudentAttendanceFinalizerTest.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `docs/02_architecture/BUSINESS_RULES.md`
- `docs/02_architecture/POSTGRESQL_SCHEMA.md`
- `codex/CHANGE_IMPACTS/FIX-ACADEMIC-TEACHER-ATTENDANCE-2026-09-11.md`
- `codex/WORK_LOG.md`

## Verification

- `StudentAttendanceFinalizerTest`, `StudentAttendanceUiTest`, `TeacherAttendanceServiceTest`: 23 test lulus, 111 assertion.
- Regression UI tambahan: `StudentAttendanceUiTest` dan `TeacherAttendanceServiceTest`: 15 test lulus, 88 assertion.
- `php artisan view:cache`: lulus.
- Route and service preserve existing cancellation semantics: cancelled sessions are excluded from teacher evaluation; live session participation remains separate for replacement teachers.
- Finalization guard verified: an explicit non-present primary teacher requires an EXPECTED substitute participation with attendance status PRESENT.
- Waka authorization verified through UI regression; non-Wali Waka no longer receives a false 403 on teacher attendance recording.

## Impact and rollback

- No database migration, deletion, identity change, or historical rewrite.
- Existing null statuses remain unresolved/missing, never inferred as absence.
- Rollback by reverting the route/form/service status extension; retain audit rows and any explicitly recorded statuses for controlled forward correction.

## Known limitation / next checkpoint

- Aktivitas pengganti terpisah seperti `Ngaji Auditorium` belum dibuat sebagai transaksi baru pada checkpoint ini. Sesi pembatalan tetap tidak dapat diisi absensi; pembuatan aktivitas pengganti perlu model sesi/aktivitas canonical agar tidak menyamarkan mata pelajaran asli.

Status: DONE
