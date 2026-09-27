# Rancangan Backfill Status Santri — 2026-09-07

Status: DRAFT — TIDAK DIEKSEKUSI

## Temuan sumber

- `students`: 94 baris pada master aplikasi, terdiri dari 84 santri historis dan 10 santri sample/pilot.
- `student_class_enrollments`: 94 baris `ACTIVE`, termasuk 10 enrollment sample/pilot.
- Seluruh enrollment efektif mulai `2026-07-01` dan belum memiliki `effective_until`.
- `student_status_history`: 0 baris.
- Data sample/pilot: 5 santri Kelas 3A dan 5 santri Kelas 3B.

## Rancangan yang direkomendasikan

Buat satu riwayat status per **84 santri historis** setelah rekonsiliasi roster:

- `status`: `ACTIVE`
- `effective_from`: `2026-07-01`
- `effective_until`: `NULL`
- `decision_reference`: referensi persetujuan resmi pemilik data
- `reason`: backfill status aktif berdasarkan enrollment aktif hasil rekonsiliasi roster Juli 2026
- `actor_user_id`: akun operator yang mendapat mandat

Jangan memasukkan 10 santri sample/pilot ke backfill historis. Dengan rancangan ini, KPI santri aktif akan menghitung santri yang benar-benar memiliki dua bukti: status aktif dan enrollment aktif.

## Validasi wajib sebelum eksekusi

1. Pemilik data menyetujui bahwa 84 roster historis berarti 84 santri aktif mulai 1 Juli 2026.
2. Pastikan tidak ada status historis yang akan tumpang tindih.
3. Tampilkan daftar 94 santri untuk rekonsiliasi nama dan kelas.
4. Simpan referensi keputusan dan audit actor.
5. Jalankan dry-run, cek jumlah target harus tepat 84 dan sample/pilot harus 0, lalu baru minta persetujuan eksekusi.

## Batasan

Dokumen ini tidak menjalankan INSERT, tidak membuat migration, dan tidak mengubah data production. Jangan mengisi status berdasarkan nama, tahun masuk, atau asumsi kenaikan kelas tanpa persetujuan pemilik data.
