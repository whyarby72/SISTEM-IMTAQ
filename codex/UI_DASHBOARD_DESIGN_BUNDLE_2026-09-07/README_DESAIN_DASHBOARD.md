# Bahan Konsep Visual Dashboard SISTEM IMTAQ

Tanggal bundle: 7 September 2026

Bundle ini disiapkan untuk menyusun konsep visual dashboard di ChatGPT sebelum implementasi dilanjutkan di Codex.

## Tujuan

Mendesain ulang tampilan dashboard agar profesional, elegan, mudah dipahami kalangan pesantren, tetap ringan dipakai, dan tidak mengubah fungsi bisnis yang sudah berjalan.

## Layar yang menjadi cakupan desain

1. Dashboard Admin/Super Admin: ringkasan data kelas, guru/staf, jadwal, kehadiran, laporan bulanan, dan menu pengelolaan.
2. Dashboard Waka Akademik: ringkasan kelas, kelengkapan kehadiran, penyelesaian sesi, dan akses laporan.
3. Dashboard Wali Kelas: ringkasan kelas yang menjadi tanggung jawabnya dan akses pengisian kehadiran.
4. Data Santri: daftar, pencarian, kelas aktif, tahun masuk, NIS/NISN, tambah, dan edit.
5. Data Kelas, Guru/Staf, dan Jadwal: daftar dan pengelolaan data akademik.
6. Laporan Bulanan: ringkasan per kelas dan rincian kehadiran per santri.

## Istilah UI yang diutamakan

- Dashboard utama dapat disebut “Ringkasan Akademik”.
- Master data → Data / Data induk.
- Attendance → Kehadiran.
- Draft → Data sementara.
- Finalisasi → Pengesahan.
- Published → Sudah disahkan.
- Snapshot → Rekap historis.
- Staff → Staf.
- Pilot → Kelas uji coba, bila istilah itu masih diperlukan.

## Business rule yang tidak boleh berubah

- Satu santri memiliki satu Student ID internal aplikasi dan satu riwayat.
- NIS/NISN boleh kosong dan dilengkapi kemudian.
- Wali Kelas mengisi kehadiran santri.
- Kehadiran dicatat per sesi; izin berlaku untuk sesi yang dipilih.
- Wali Kelas dapat menambahkan catatan khusus.
- Rekap Juli 2026 adalah data historis dan tidak boleh diubah maknanya oleh desain visual.
- Laporan bulanan memiliki rincian per kelas dan per santri.
- Super Admin tetap memiliki akses penuh sesuai aturan yang sudah berjalan.
- Waka Akademik berperan sebagai pemeriksa/pengesah laporan sesuai alur yang telah ditetapkan.

## Data referensi visual saat ini

- 5 kelas aktif: Kelas 1, 2A, 2B, 3A, 3B.
- 84 santri pada data historis Juli 2026.
- Contoh laporan Kelas 3A: 15 santri, hadir 268, izin 26, sakit 6, absen 0.
- Contoh laporan Kelas 3B: hadir 348, izin 13, sakit 5, absen 2.
- Rincian Arab dan Indonesia harus tetap terbaca; nama Arab menggunakan arah teks kanan-ke-kiri.

## Arahan desain

- Utamakan bahasa Indonesia yang familiar di pesantren.
- Gunakan hierarki visual jelas: judul, ringkasan angka, tindakan utama, lalu tabel/rincian.
- Beri ruang antar blok yang cukup; jangan menempel antar kartu.
- Pastikan desktop dan mobile sama-sama nyaman.
- Tabel lebar harus memiliki pembungkus horizontal atau versi kartu mobile.
- Status harus memiliki warna dan teks yang tetap jelas tanpa hanya mengandalkan warna.
- Tombol tindakan utama harus mudah ditemukan, tetapi tidak mendominasi seluruh halaman.
- Pertahankan nuansa hijau yang tenang, bersih, profesional, dan elegan; perubahan warna besar perlu alasan desain.

## Batasan implementasi

Bundle ini hanya bahan desain. Implementasi final harus dilakukan di repository aplikasi, dengan perubahan minimum, test terarah, dan tanpa mengubah database/business rule kecuali disetujui sebagai task terpisah.

## Isi folder

- `source/views/`: view Blade yang membentuk dashboard dan layar Academic/Admin terkait.
- `source/controllers/`: controller yang memasok data ke layar tersebut.
- `source/tests/`: test fitur dan service yang menjadi pagar perilaku.
- `source/routes-web.php`: route web untuk memahami navigasi dan akses layar.
- `DESIGN_HANDOFF_PROMPT.md`: prompt siap pakai untuk meminta konsep visual di ChatGPT.
