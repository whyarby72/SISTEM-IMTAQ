# Change Impact — FIX-ACADEMIC-TEACHER-ATTENDANCE

Tanggal: 2026-09-11
Owner module: Academic / Attendance
Change class: `MODULE_CONTRACT`

## Requested outcome

Mencatat kehadiran guru per sesi untuk evaluasi, membedakan ketidakhadiran guru dari pembatalan resmi pesantren, dan memastikan sesi tetap dapat diabsen ketika guru digantikan.

## Scope and policy

- Pembatalan resmi karena kegiatan pesantren tidak dihitung sebagai ketidakhadiran guru.
- Guru yang berhalangan tetapi sesi tetap berjalan dicatat sebagai `ABSENT`, `SICK`, `IZIN`, atau `OTHER` dengan alasan.
- Guru pengganti atau wali kelas memiliki participation terpisah; kehadiran santri tetap mengikuti sesi yang berlangsung.
- Tidak ada migration database; kolom `attendance_status` yang sudah ada menerima nilai baru melalui service contract.

## Impact

- Source of truth: `session_teacher_participations.attendance_status` dan `reason`.
- Affected consumers: Academic session page, Waka dashboard teacher-attendance summary, audit log.
- RBAC: pencatatan tetap melewati `WaliKelasContextResolver`; Waka/Super Admin scope existing tetap berlaku.
- Historical data: tidak diubah; status kosong tetap berarti belum dicatat, bukan absen.
- Rollback: nonaktifkan route/form dan kembalikan resolver status ke daftar lama; data status baru dipertahankan untuk forward-compatible correction.

## Regression scope

- `TeacherAttendanceServiceTest`
- `AcademicRoleDashboardServiceTest`
- `StudentAttendanceUiTest`
- Blade view cache

Status: IMPLEMENTED
