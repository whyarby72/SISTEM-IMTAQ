# Kontrak Import Historis Rekap Kehadiran Juli 2026

**Task:** `IMP-MIG-001`  
**Status:** `CONTRACT_READY_FOR_DRY_RUN`  
**Tanggal:** 2026-09-04

## Tujuan

Memasukkan rekap kehadiran santri Juli 2026 sebagai data historis tingkat kelas, dengan jejak sumber dan rekonsiliasi yang dapat diaudit. Data ini bukan pengganti transaksi kehadiran per sesi yang sudah ada atau yang akan dibuat untuk periode baru.

## Sumber dan provenance

| Sumber | Peran | SHA-256 |
|---|---|---|
| `/Users/afradadmedia/Downloads/IMTAQ_ATTENDANCE_JULY_2026_CODEX_HANDOFF.md` | business rule dan architectural contract | `882dda68b575f9c1869a6ca2d8630521acebf80148274e21ac158428faefa4e5` |
| `/Users/afradadmedia/Downloads/IMTAQ_ATTENDANCE_JULY_2026_SEED.json` | data referensi rekap | `b31cf6a64a6f35a9131edd1d40d0cdb5e6da4bee1db0950eb7e8aa8a56761a6d` |

File asli wajib dipertahankan pada import batch. Checksum, periode sumber `2026-07`, pemilik sumber, waktu penerimaan, dan status validasi dicatat sebagai provenance. Seed berstatus `WORKING_COMPLETE_ROSTER` dan `NOT_VALIDATED_LOCKED`; import tidak boleh langsung dianggap terkunci atau dipublikasikan.

## Bentuk data yang diizinkan

- Granularitas import: `MONTHLY_SUMMARY`.
- Satu baris staging = satu ringkasan `class_admin`: `1`, `2A`, `2B`, `3A`, `3B`.
- Ringkasan `Attendance_Group` `2B-3B` dan total keseluruhan hanya dipakai untuk rekonsiliasi/tampilan turunan, bukan baris fakta tambahan, agar tidak terjadi double count.
- Tidak membuat tanggal, sesi, waktu, atau baris kehadiran individual santri yang tidak ada di sumber.
- Tidak membuat status teacher attendance dari angka rekap santri.
- `PERMISSION` dari sumber ditampilkan sebagai **Izin**, `SICK` sebagai **Sakit**, dan `ABSENT` sebagai **Tidak hadir**. Ini adalah pemetaan tampilan/import historis; enum dan alur kehadiran sesi saat ini tidak diubah pada task ini.
- `NON_ELIGIBLE` tetap terpisah dari `ABSENT`, dengan alasan Juli `TAHFIZH_CATCHUP`.

## Mapping yang dipertahankan

| `Class_Admin` | `Attendance_Group` | `MATIQ_Report_Class` | Roster |
|---|---|---|---:|
| `1` | `1` | `Kelas 1` | 20 |
| `2A` | `2A` | `Kelas 2` | 19 |
| `2B` | `2B-3B` | `Kelas 2` | 10 |
| `3A` | `3A` | `Kelas 3` | 15 |
| `3B` | `2B-3B` | `Kelas 3` | 20 |

Mapping ini hanya dapat diarahkan ke identitas kelas kanonik setelah pemeriksaan operator. Import tidak boleh otomatis membuat kelas baru berdasarkan nama atau membuat pecahan `1A/1B` yang tidak ada di sumber.

## Aturan perhitungan dan rekonsiliasi

- `eligible = present + permission + sick + absent`.
- `attendance_rate = present / eligible`; denominator bersifat dinamis.
- `non_eligible` bukan ketidakhadiran.
- Pembatalan institusional tidak masuk denominator.
- Data yang hilang tidak dianggap nol.
- Ekspektasi seed: roster `84`, eligible `1.198`, non-eligible `142`, peluang terjadwal `1.340`, present `1.126`, permission `39`, sick `29`, absent `4`, dan tingkat hadir `93,99%`.
- Rekonsiliasi grup `2B-3B`: present `509`, eligible `538`, non-eligible `62`, rate `94,61%` (pembulatan dua desimal).
- Unit jadwal Juli (`1=3`, `2A=20`, `3A=20`, `2B-3B=20`) hanya asumsi rekonsiliasi periode ini; tidak boleh menjadi aturan global.

Rekonsiliasi batch wajib memenuhi: `Source Total = Imported + Rejected + Quarantined + Duplicate + Explicitly Excluded`. Setiap baris sumber harus memiliki hasil, termasuk bila hasilnya quarantine atau rejected.

## Alur aman

Gunakan tabel import yang sudah tersedia: satu `import_batch`, satu `import_file` dengan granularitas `MONTHLY_SUMMARY`, lima `import_rows` pada staging, error/quarantine bila perlu, mapping kelas setelah review, lineage ke sumber, lalu dry-run dan rekonsiliasi. Dry-run tidak membuat baris fakta kanonik.

Import nyata baru boleh dilakukan setelah:

1. identitas lima kelas dan pemilik sumber dikonfirmasi;
2. dry-run lulus tanpa blocking error;
3. total dan rumus di atas cocok;
4. ada persetujuan pemilik bisnis/otoritas akademik sesuai prosedur.

## Konflik yang sengaja tidak diubah

Kontrak Juli memakai lifecycle `DRAFT → SUBMITTED → VALIDATED → LOCKED → PUBLISHED`, sedangkan alur sesi lokal saat ini memakai lifecycle yang lebih sederhana. Keduanya dipisahkan: lifecycle batch import mengatur provenance dan publikasi rekap historis; lifecycle attendance sesi tetap utuh. Perbedaan label `PERMISSION`/`Izin` dan ketiadaan opsi `SICK` pada UI lokal juga tidak diubah diam-diam. Perubahan kontrak tersebut memerlukan task dan keputusan terpisah.

## Blocker sebelum implementasi dry-run

Sumber hanya agregat per kelas. Karena itu, data ini belum dapat digunakan untuk laporan per-santri, koreksi per-sesi, atau pengaitan ke student attendance individual. Scope aman berikutnya adalah profiler/validator dry-run dan review mapping; bukan eksekusi import.
