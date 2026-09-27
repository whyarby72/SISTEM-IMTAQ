# Change Manifest

- **Change ID:** IMP-ACADEMIC-WORKFLOW-SIMPLIFICATION-2-2026-09-10
- **Task ID:** IMP-ACADEMIC-WORKFLOW-SIMPLIFICATION-2-2026-09-10
- **Title:** Sederhanakan input jadwal dan pembuatan roster massal
- **Date:** 2026-09-10
- **Owner module/workstream:** Academic Admin / Attendance Operations
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Admin dapat membuat jadwal dengan memilih konsep akademik yang mudah dipahami, tanpa harus mencari kode penugasan; Waka dapat menyiapkan roster untuk seluruh sesi kosong dari satu aksi.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact
- **Affected modules/workstreams:** Academic scheduling and attendance exceptions.
- **Source-of-truth entities/services affected:** TeachingAssignment, ScheduleRule, ClassSession, SessionStudentParticipant, SessionParticipantSnapshotter.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Existing Waka/Super Admin checks retained; official non-pilot scope enforced.
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing `teaching_assignment_id` submissions remain supported; no historical record is deleted or rewritten.
- **Environment/config variables changed:** NONE

## Files
- **Files modified:** `application/web/app/Http/Controllers/Admin/ScheduleRuleController.php`; `application/web/app/Http/Controllers/Academic/AttendanceExceptionController.php`; `application/web/app/Http/Controllers/Admin/StudentController.php`; `application/web/app/Http/Controllers/Admin/AcademicClassController.php`; `application/web/app/Http/Controllers/Admin/AcademicStructureController.php`; `application/web/routes/web.php`; schedule edit/create, attendance exception, student, class, and structure views; shared academic sidebar; related feature tests; work log.
- **Files added:** `application/web/resources/views/admin/academic/schedules/edit-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/students/index-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/students/create-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/students/edit-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/classes/index-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/classes/create-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/classes/edit-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/structure/index-sidebar-proper.blade.php`; `application/web/resources/views/admin/academic/structure/index-sidebar-refined.blade.php`; `application/web/resources/views/admin/academic/structure/homeroom-edit-sidebar-proper.blade.php`; this manifest.
- **Files deleted:** `application/web/resources/views/admin/academic/students/index-sidebar.blade.php` (replaced after Blade parse-error cleanup).
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** Targeted 31 tests / 118 assertions; previous full Academic suite 165 tests / 592 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout
- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert application changes; no database rollback required.
- **Known limitations:** Date-range bulk roster action is scoped to the sessions currently visible after list filtering; it does not create roster for archived/cancelled sessions.
- **Follow-up fix:** Pilot semesters are excluded from both schedule-preparation forms by the academic-year scope filter.
- **Follow-up fix:** Tambah Jadwal kini memakai layout sidebar akademik bersama agar navigasi konsisten.
- **Follow-up fix:** Tambah Penugasan Mengajar dan Tambah Mata Pelajaran juga memakai layout/sidebar akademik bersama.
- **Follow-up fix:** Sidebar akademik menyediakan akses langsung ke Mata Pelajaran dan Guru/Staf untuk peran pengelola akademik.
- **Follow-up fix:** Daftar Mata Pelajaran kini menggunakan layout/sidebar akademik bersama.
- **Follow-up fix:** Halaman daftar, tambah, dan edit Guru/Staf kini menggunakan layout/sidebar akademik bersama.
- **Follow-up fix:** Daftar Kelas kini menggunakan tampilan sidebar akademik bersama.
- **Follow-up fix:** Edit Mata Pelajaran kini menggunakan layout/sidebar akademik bersama.
- **Follow-up fix:** Form Tambah Mata Pelajaran dirapikan dengan hierarki informasi dan aksi yang lebih jelas.
- **Follow-up fix:** Daftar Guru/Staf mendapat CTA langsung menuju Tambah Penugasan dan Jadwal.
- **Follow-up fix:** Daftar Jadwal mendapat CTA langsung menuju penyiapan mapel, guru, dan penugasan.
- **Follow-up fix:** Daftar Jadwal mendapat filter Semester resmi untuk memisahkan jadwal Semester I dan II.
- **Follow-up fix:** Breakpoint daftar Jadwal diperluas agar toolbar dan CTA responsif pada viewport dengan sidebar.
- **Follow-up fix:** Daftar Santri kini menggunakan sidebar akademik bersama dengan menu Santri aktif; fungsi filter, pagination, NIS/NISN, dan edit tetap tersedia.
- **Follow-up fix:** Form Tambah Santri dan Edit Santri kini menggunakan sidebar akademik bersama dan layout responsif.
- **Follow-up fix:** Daftar, Tambah, dan Edit Kelas kini menggunakan sidebar akademik bersama dan layout responsif.
- **Follow-up fix:** Edit/Revisi Jadwal kini menggunakan sidebar akademik bersama dan layout responsif tanpa mengubah alur revisi atau arsip historis.
- **Follow-up fix:** Halaman Struktur Akademik dan Edit Wali Kelas kini menggunakan sidebar akademik bersama dan layout responsif.
- **Follow-up fix:** Form Tambah Wali Kelas dirapikan agar tidak meluber pada desktop dan responsif pada mobile.
- **Follow-up fix:** Jarak tombol aksi pada form struktur akademik diperbaiki agar blok form tidak menempel.
- **Follow-up fix:** Input tanggal Struktur Akademik menampilkan `dd/mm/yyyy` dan dinormalisasi aman di backend.
- **Follow-up fix:** Aksi roster massal pada daftar temuan kehadiran diberi panel dan jarak visual yang tepat.
- **Follow-up fix:** Tombol roster massal dimodernisasi dengan ikon dan state interaksi yang lebih jelas.
- **Follow-up fix:** Kartu KPI Dashboard ditata satu kolom pada mobile kecil agar lebih mudah dibaca.
- **Follow-up fix:** Keterangan rentang pada filter Dashboard dipisahkan ke baris penuh pada mobile.
- **Follow-up fix:** Aksi pada halaman Isi Kehadiran Santri dimodernisasi dengan state interaksi yang konsisten.
- **Follow-up fix:** Kartu Pemantauan Kelas dirapikan untuk pemindaian informasi yang lebih baik di mobile.
- **Follow-up fix:** Status dan aksi Edit pada tabel kelas diperjelas secara visual.
- **Follow-up fix:** Status dan aksi Edit pada tabel Mata Pelajaran diperjelas, dengan penjelasan status histori.
- **Follow-up fix:** Status dan aksi Edit pada tabel Guru/Staf diperjelas secara visual.
- **Follow-up fix:** Daftar Santri kini mengambil seluruh enrollment aktif pada kelas resmi tahun ajaran aktif, termasuk hasil rekonsiliasi roster, tanpa menampilkan data pilot.
- **Follow-up fix:** Halaman Kehadiran Santri diperkuat untuk tablet/mobile dengan pembungkusan metadata dan blok informasi; tabel peserta tetap memakai scroll horizontal yang terkontrol.
- **Follow-up fix:** Proses pengesahan kehadiran yang belum lengkap kini memberi feedback terarah dan kembali ke halaman input, tanpa 500 mentah; validasi domain tetap dipertahankan.
- **Follow-up fix:** Tombol pengesahan kehadiran menggunakan data dari form input yang sama dan menyimpannya sebelum finalisasi, sehingga pilihan “Tandai semua” tidak hilang.
- **Follow-up fix:** Koneksi HTML tombol pengesahan dipertegas agar style modern dan seluruh data form tetap terkirim ke endpoint finalisasi.
- **Follow-up fix:** Navigasi dari detail kehadiran mempertahankan filter bulan, kelas, dan urutan daftar sesi melalui tautan kembali dan menu sidebar.
- **Follow-up fix:** Header Kehadiran Santri membedakan Guru pengajar dari jadwal dan Diisi oleh/Diperiksa oleh sebagai petugas transaksi.
- **Follow-up fix:** Label petugas pengisian diperjelas menjadi “Diabsen oleh”, sementara hasil final tetap “Diperiksa oleh”.
- **Follow-up fix:** Detail Kehadiran Santri mendapat spacing mobile yang aman dari sidebar dan ringkasan satu kolom agar tidak terpotong.
- **Follow-up fix:** Metadata sesi kehadiran menampilkan nama hari berbahasa Indonesia agar waktu sesi lebih jelas.
- **Follow-up fix:** Alur koreksi pasca-pengesahan tersedia dari detail sesi hingga review dan penerapan oleh Waka Akademik, dengan audit/versioning dan tanpa penghapusan histori.
- **Follow-up fix:** Sidebar pengelola akademik menyediakan akses langsung ke Hasil & Koreksi.
- **Follow-up fix:** Waka Akademik memperoleh aksi koreksi langsung pada halaman Periksa hasil, dengan alasan, version check, dan audit khusus Waka tanpa menghapus histori.
- **Follow-up fix:** Binding dependency endpoint koreksi langsung diperbaiki ke service domain yang benar.
- **Follow-up fix:** Validasi ID attendance diselaraskan dengan tabel canonical `student_attendance` agar endpoint koreksi tidak gagal sebelum service dijalankan.
- **Follow-up fix:** Form koreksi hasil kehadiran diperkuat untuk mobile dengan batas lebar, box sizing, wrapping field, dan tombol full-width.
- **Follow-up fix:** Label aksi Waka diperjelas menjadi “Terapkan koreksi” dan halaman Hasil & Koreksi menjelaskan bahwa koreksi langsung tidak masuk antrean persetujuan.
- **Follow-up fix:** Aksi status massal dan status individual pada form kehadiran kini memberi perubahan visual langsung sebelum penyimpanan.
- **Follow-up fix:** Status kehadiran “Sakit” ditambahkan secara end-to-end pada input, koreksi, validasi, ringkasan, dan ekspor laporan.
- **Follow-up fix:** Halaman Hasil Kehadiran Santri diperkuat untuk tablet dan mobile dengan grid ringkasan adaptif, metadata wrapping, dan tabel scroll terisolasi.
- **Follow-up fix:** Tabel hasil baca-saja diubah menjadi kartu santri responsif pada tablet/mobile agar seluruh kolom, termasuk status pemeriksaan, tetap terbaca.
- **Follow-up fix:** Jarak antara tombol unduhan dan kartu hasil pertama diperbaiki agar blok visual tidak menempel.
- **Follow-up fix:** Tabel pengisian kehadiran dibuat menjadi kartu responsif pada tablet/mobile agar dropdown dan catatan tidak terpotong.
- **Status:** DONE
