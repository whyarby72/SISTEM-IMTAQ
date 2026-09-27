# Review Mapping dan Pemilik Sumber — Juli 2026

**Task:** `IMP-MIG-003`  
**Status:** `PARTIAL_RUNTIME_MAPPING_CONFIRMED_IMPORT_BLOCKED`  
**Tanggal:** 2026-09-04

## Keputusan yang dapat dikunci dari sumber

Mapping sumber berikut diterima tanpa perubahan:

| Class_Admin | Attendance_Group | MATIQ reporting | Roster |
|---|---|---|---:|
| `1` | `1` | `Kelas 1` | 20 |
| `2A` | `2A` | `Kelas 2` | 19 |
| `2B` | `2B-3B` | `Kelas 2` | 10 |
| `3A` | `3A` | `Kelas 3` | 15 |
| `3B` | `2B-3B` | `Kelas 3` | 20 |

Tidak boleh membuat `1A`/`1B`, menggabungkan 2B dan 3B menjadi satu fakta kelas, atau menambahkan baris total/grup sebagai fakta baru.

## Pemilik dan validator

- Pemilik/validator menurut handoff: **Waka Academic**.
- Identitas operasional yang sudah diberikan: **Wawan SN**.
- Persetujuan publikasi historis: tetap memerlukan persetujuan otoritas akademik/pemilik bisnis sesuai prosedur; review teknis ini bukan tanda publish.

## Rekonsiliasi yang diterima

Dry-run `IMP-MIG-002` lulus dengan checksum sumber cocok, lima baris kelas, roster 84, peluang 1.340, eligible 1.198, non-eligible 142, hadir 1.126, dan rate 93,99%. Tidak ada fakta kanonik dibuat.

## Blocker runtime

Pemeriksaan read-only ke database kini berhasil. Hasilnya:

| Class_Admin | Hasil runtime | Wali Kelas aktif |
|---|---|---|
| `1` | Belum ada | Belum dapat diverifikasi |
| `2A` | Belum ada | Belum dapat diverifikasi |
| `2B` | Belum ada | Belum dapat diverifikasi |
| `3A` | Ada, aktif; ID `01a06a98-4fbb-73a5-a827-4a4535d6e1b9` | Azhar |
| `3B` | Ada, aktif; ID `01a06a98-4fea-73a0-ab58-6967d2126baf` | Azhar |

Wawan SN tercatat sebagai Staff aktif dengan kode `PILOT-WAWAN-SN`; ia tetap berperan sebagai validator Waka Academic, bukan Wali Kelas pada hasil query ini.

## Target assignment dari pemilik bisnis

Target data yang akan digunakan setelah alur Admin siap:

| Class_Admin | Wali Kelas target |
|---|---|
| `1` | Ust. Alwan |
| `2A` | Ust. Afwa |
| `3A` | Ust Azhar |
| `2B` | Ust. Maulana |
| `3B` | Ust. Maulana |

Validator/Waka Akademik target: **Ust. Wawan**.

Nama-nama ini adalah input target master. Belum ada penulisan Staff, GradeLevel, Class, atau HomeroomAssignment dari input ini.

Karena tiga kelas belum ada, import seluruh dataset 84 santri tetap diblokir. Karena itu:

- mapping sumber sudah diterima;
- mapping primary key kanonik baru terverifikasi untuk 3A dan 3B;
- tidak boleh auto-create kelas atau import parsial tanpa keputusan scope;
- langkah berikutnya adalah membuat/menetapkan master kelas 1, 2A, dan 2B melalui alur admin, lalu mengulangi pemeriksaan read-only.

Lifecycle attendance sesi, eligibility, denominator, dan mapping MATIQ tidak diubah.
