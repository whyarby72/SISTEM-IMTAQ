# Review Rancangan Snapshot Kehadiran Santri Bulanan

**Task:** `IMP-MIG-015`  
**Tanggal:** 2026-09-06  
**Status:** REVIEWED — belum ada migration atau data production baru

## Keputusan konsep

`monthly_student_attendance_snapshots` diperlakukan sebagai arsip agregat per-santri per-periode, bukan tabel transaksi kehadiran harian. Tabel ini tidak boleh memiliki atau menghasilkan `session_id`, `attendance_date`, jam hadir, urutan sesi, atau status attendance sesi.

## Struktur yang direkomendasikan

Satu baris mewakili satu `source_record_id` untuk satu santri pada satu periode sumber.

- Identitas: `id` internal UUID; `student_id` FK ke `students` setelah mapping disetujui.
- Konteks kelas saat periode: `class_id` FK ke `classes`, `class_admin`, `attendance_group`, dan `matiq_report_class` sebagai snapshot konteks sumber.
- Periode dan lineage: `period` format `YYYY-MM`, `source_record_id`, `source_checksum`, `import_batch_id`, `import_file_id`, dan `import_row_id`.
- Nilai agregat: `scheduled_attendance_units_working`, `present`, `permission`, `sick`, `absent`, `eligible`, `non_eligible`, `non_eligible_reason`, dan `attendance_rate`.
- Provenance: `source_absent_term` dan `raw_payload` tetap dipertahankan bila diperlukan untuk nilai yang belum memiliki padanan kanonik.

`source_record_id` hanya untuk lineage, bukan primary key. Rekomendasi uniqueness adalah kombinasi `period + student_id + source_checksum`, dengan pencegahan duplikasi source row berdasarkan `import_row_id`/checksum.

## Business rule yang dipertahankan

- `eligible = present + permission + sick + absent`.
- `attendance_rate = present / eligible`; denominator dinamis.
- `non_eligible` bukan ketidakhadiran dan tidak masuk denominator.
- Tidak ada tanggal/sesi yang boleh ditebak dari jumlah bulanan atau jadwal.
- Nilai kosong tidak diubah menjadi nol.
- Mapping `Class_Admin`, `Attendance_Group`, dan `MATIQ_Report_Class` tetap sama.
- Lifecycle batch import tetap `DRAFT → SUBMITTED → VALIDATED → LOCKED → PUBLISHED`. Snapshot mengikuti keputusan batch dan tidak membuat lifecycle attendance sesi baru.
- Koreksi historis dilakukan melalui batch koreksi/audit baru; baris snapshot yang telah diterima tidak diedit diam-diam.

## Rekonsiliasi sebelum promosi dari staging

Wajib lulus: 84 roster, 84 snapshot, seluruh baris terpetakan ke canonical `students.student_id` dan kelas aktif historis, tidak ada source row yatim/duplikat, serta total dan formula Juli tetap `eligible 1.198`, `non-eligible 142`, `present 1.126`, `permission 39`, `sick 29`, `absent 4`, rate `93,99%`.

## Temuan penting

Staging saat ini berisi 84 baris roster dan 84 baris attendance, tetapi `student_id` staging masih NULL. Karena itu migration tabel saja belum aman. Implementasi berikutnya harus menambahkan proses mapping berbasis `source_record_id` ke canonical UUID, lalu validasi dan lineage; tidak boleh menganggap nama sebagai ID atau langsung menyalin staging ke production.

## Batas langkah ini

Belum membuat tabel, model, service import, route, atau mengubah data. Langkah implementasi berikutnya memerlukan migration additive, validator/mapping yang idempotent, dan targeted tests untuk grain bulanan, lineage, formula, duplicate, unresolved mapping, dan larangan session expansion.
