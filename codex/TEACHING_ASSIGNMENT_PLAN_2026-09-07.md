# Rancangan Tugas Mengajar Academic — 2026-09-07

Status: DRAFT — TIDAK DIEKSEKUSI

## Temuan saat ini

- Staf aktif: 7.
- `TeachingAssignment` berstatus `ACTIVE`: 0.
- Semester aktif: `Semester 1 Pilot`, 1 Juli–31 Desember 2026.
- Mata pelajaran aktif yang tersedia: `Tahfizh Sample Pilot`.
- Kelas aktif pada scope dashboard Waka: Kelas 3A (Pilot) dan Kelas 3B (Pilot).
- Layar Admin Jadwal saat ini memilih `TeachingAssignment` yang sudah ada; layar tersebut belum membuat tugas mengajar baru.

## Data minimum yang harus ditetapkan

Setiap tugas mengajar memerlukan:

1. Guru/staf pengajar yang benar-benar ditunjuk.
2. Kelas.
3. Mata pelajaran resmi, bukan mata pelajaran sample/pilot jika sistem mulai memakai data produksi.
4. Semester.
5. Tanggal efektif mulai dan selesai.
6. Status workflow yang disetujui sesuai kewenangan Admin Akademik.

## Rekomendasi implementasi

- Tambahkan alur Admin Akademik untuk membuat dan mengelola `TeachingAssignment` sebelum membuat aturan jadwal.
- Validasi guru aktif, kelas aktif, mata pelajaran aktif, semester aktif, dan rentang tanggal tidak tumpang tindih.
- Setelah tugas mengajar disetujui, barulah Admin membuat aturan jadwal mingguan.
- Sesi yang dihasilkan menjadi sumber resmi untuk KPI guru aktif dan status sesi santri.
- Jangan menetapkan Wali Kelas otomatis sebagai guru mata pelajaran tanpa penugasan resmi.

## Batasan

Dokumen ini tidak membuat assignment, jadwal, sesi, atau migration. Data sample/pilot tidak dijadikan tugas mengajar produksi.
