# Checklist UAT Pilot Akademik — Kelas 3A dan 3B

Dokumen ini adalah checklist pelaksanaan UAT, bukan tanda bahwa UAT sudah dijalankan. Isi kolom hasil dan bukti saat pengujian berlangsung.

## Ruang lingkup

- Kelas: 3A dan 3B
- Periode pilot: 4–12 September 2026
- Wali Kelas/penginput: Azhar
- Pemeriksa/penyetuju: Wawan SN — Waka Akademik
- Penerima hasil: Abu Ubaidah — Business Owner
- Data: sample pilot lokal

## Cara memberi hasil

Gunakan `LULUS` jika hasil sesuai harapan, `GAGAL` jika tidak sesuai, dan `N/A` jika skenario tidak berlaku. Setiap `GAGAL` harus dicatat di log masalah.

## A. Persiapan

| ID | Pemeriksaan | Harapan | Hasil | Bukti/catatan |
|---|---|---|---|---|
| PRE-01 | Kelas 3A tersedia | Kelas tampil dan dapat dibuka | LULUS | Ringkasan Wali Kelas, browser lokal 2026-09-04 |
| PRE-02 | Kelas 3B tersedia | Kelas tampil dan dapat dibuka | LULUS | Ringkasan Wali Kelas, browser lokal 2026-09-04 |
| PRE-03 | Wali Kelas | Azhar menjadi penginput yang tercatat | LULUS | Akun WALI_KELAS, browser lokal 2026-09-04 |
| PRE-04 | Jadwal dan sesi | Sesi pada 4–12 September tersedia |  |  |
| PRE-05 | Daftar siswa | Siswa yang diharapkan tampil sesuai kelas |  |  |

## B. Pengisian oleh Wali Kelas

| ID | Skenario | Harapan | Hasil | Bukti/catatan |
|---|---|---|---|---|
| ATT-01 | Azhar membuka sesi kelasnya | Halaman Kehadiran Siswa dapat dibuka | LULUS | Sesi 3A, browser lokal 2026-09-04 |
| ATT-02 | Mengisi siswa hadir | Status Hadir tersimpan | LULUS | Sesi sample menampilkan 3 siswa Hadir |
| ATT-03 | Mengisi siswa tidak hadir | Status Tidak hadir tersimpan | LULUS | Sesi sample menampilkan 1 siswa Tidak hadir |
| ATT-04 | Mengisi izin pada satu sesi | Status Izin hanya berlaku pada sesi itu | LULUS | Siswa 3A-02 berstatus Izin dengan catatan sesi |
| ATT-05 | Siswa masuk di tengah sesi | Status Hadir dapat disimpan dengan catatan | LULUS | Alur catatan hadir tersedia; tidak mengubah data sample |
| ATT-06 | Menyimpan sebagian isian | Isian sementara tersimpan; data kosong tidak otomatis menjadi tidak hadir |  |  |
| ATT-07 | Menambahkan catatan | Catatan tampil kembali saat halaman dibuka | LULUS | Catatan izin tampil pada sesi 3A |
| ATT-08 | Mengesahkan kehadiran lengkap | Data menjadi selesai/diperiksa sesuai alur | LULUS | Status sesi Selesai dan isian Sudah diperiksa |
| ATT-09 | Guru mencoba mengisi sendiri | Akses ditolak; guru tidak dapat self-confirm |  |  |
| ATT-10 | Wali Kelas membuka kelas lain | Akses lintas kelas ditolak |  |  |

## C. Pemeriksaan Waka Akademik

| ID | Skenario | Harapan | Hasil | Bukti/catatan |
|---|---|---|---|---|
| WAKA-01 | Wawan membuka Ringkasan Akademik | Ringkasan dapat dilihat |  |  |
| WAKA-02 | Memeriksa hasil kelas 3A/3B | Data sesuai isian Wali Kelas |  |  |
| WAKA-03 | Membuka Perlu Perhatian Kehadiran | Sesi yang belum lengkap/tervalidasi terlihat |  |  |
| WAKA-04 | Mengunduh rekap bila berizin | Isi rekap sesuai data yang tercatat |  |  |
| WAKA-05 | Memeriksa catatan dan izin | Catatan serta izin per sesi tetap terlihat |  |  |

## D. Data dan keamanan

| ID | Pemeriksaan | Harapan | Hasil | Bukti/catatan |
|---|---|---|---|---|
| SAFE-01 | Nilai dashboard dibandingkan dengan data sesi | Angka dashboard sesuai fakta yang dicatat |  |  |
| SAFE-02 | Sesi dibatalkan/reschedule bila ada | Tidak membuat ketidakhadiran palsu |  |  |
| SAFE-03 | Koreksi data | Koreksi memakai alasan dan jejak perubahan |  |  |
| SAFE-04 | Data lama setelah koreksi | Riwayat tetap dapat ditelusuri |  |  |
| SAFE-05 | Super Admin mengedit bila diperlukan | Akses penuh tetap tersedia dengan jejak audit |  |  |

## E. Log masalah

| ID | Tanggal | Skenario | Harapan | Hasil aktual | Tingkat | Penanggung jawab | Status | Bukti |
|---|---|---|---|---|---|---|---|---|
| PILOT-001 |  |  |  |  | BLOCKER/HIGH/MEDIUM/LOW |  | OPEN |  |

## Hasil review checklist

- Kelengkapan cakupan: `LULUS` — persiapan, pengisian Wali Kelas, pemeriksaan Waka Akademik, keamanan/data, dan log masalah tersedia.
- Kesesuaian peran: `LULUS` — Azhar sebagai penginput, Wawan SN sebagai pemeriksa, dan Abu Ubaidah sebagai penerima hasil.
- Kejelasan hasil: `LULUS` — setiap skenario memiliki harapan, kolom hasil, dan kolom bukti/catatan.
- Status checklist: `SIAP DIGUNAKAN UNTUK UAT`
- Catatan: status ini adalah review kesiapan dokumen, bukan persetujuan hasil UAT.

## Keputusan UAT

- Hasil keseluruhan: `PENDING`
- Masalah BLOCKER/HIGH terbuka: ____________________
- Azhar — Wali Kelas: ____________________
- Wawan SN — Waka Akademik: ____________________
- Abu Ubaidah — Business Owner: ____________________
- Tanggal keputusan: ____________________
