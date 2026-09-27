# Bundle Audit UI — Perlu Perhatian Kehadiran

Bundle ini berisi source dan konteks minimum untuk audit serta pemolesan UI/UX halaman **Kontrol Kualitas Data → Perlu Perhatian Kehadiran**.

## Batasan audit

- Fokus utama: tampilan, hierarki visual, responsif, kejelasan filter, tabel temuan, preview pembatalan massal, dan feedback aksi.
- Jangan mengubah business rule, otorisasi, route, payload, query kandidat, transaksi, atau data historis tanpa instruksi terpisah.
- `Pratinjau` adalah GET/read-only. Pembatalan baru terjadi melalui POST setelah alasan diisi dan konfirmasi.
- Jangan menyertakan atau meminta `.env`, credential, secret, database dump, cache, atau `vendor`.

## Prompt untuk ChatGPT

Audit bundle ini sebagai senior product designer + Laravel UI reviewer. Evaluasi halaman Perlu Perhatian Kehadiran secara mendalam dari source yang tersedia dan screenshot pengguna. Berikan:

1. diagnosis masalah UI/UX yang nyata;
2. rekomendasi modern yang efektif untuk desktop, tablet, dan mobile;
3. prioritas P0/P1/P2 dengan alasan;
4. rancangan ulang hierarki halaman, filter daftar temuan, tabel, empty state, dan pembatalan sesi massal;
5. perhatian accessibility (keyboard, focus, label, kontras, touch target, screen reader);
6. usulan copywriting Bahasa Indonesia yang operasional;
7. acceptance criteria dan test UI yang perlu ditambahkan;
8. patch plan yang hanya menyentuh presentation bila memungkinkan.

Jadikan source repository sebagai technical source of truth. Jangan mengarang route, field, atau behavior yang tidak ada di bundle. Tandai bagian yang memerlukan verifikasi repository penuh.

## Isi bundle

- `source/views/exceptions.blade.php` — halaman utama.
- `source/controllers/AttendanceExceptionController.php` — filter, preview, dan endpoint terkait.
- `source/services/AttendanceExceptionMonitor.php` — pembentukan temuan.
- `source/services/CancellationService.php` — aturan dan transaksi pembatalan.
- `source/routes/web.php` — route attendance terkait.
- `source/partials/` — sidebar dan style shell yang dipakai halaman.
- `source/models/` — model yang diperlukan untuk memahami relasi halaman.
- `tests/AttendanceExceptionUiTest.php` — regression/UI feature tests.

Generated: 2026-09-13.
