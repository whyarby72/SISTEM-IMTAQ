# SISTEM IMTAQ — Checkpoint Bundle

Tanggal bundle: 11 September 2026

Bundle ini disiapkan untuk audit mendalam terhadap sistem sampai checkpoint lokal terakhir. Cakupan utama meliputi source Laravel, routes, views, domain services, migrations, tests, dokumentasi arsitektur, serta catatan perubahan Codex.

## Cara menggunakan

1. Unggah file ZIP ini ke ChatGPT.
2. Minta audit berdasarkan prioritas: correctness bisnis, RBAC/IDOR, konsistensi transaksi attendance, migration safety, test coverage, dan responsive UI.
3. Minta temuan dikembalikan dengan severity P0–P3, bukti file/baris, dampak, dan patch yang disarankan.

## Sengaja tidak disertakan

- `application/web/.env` dan secrets.
- `vendor/`, `node_modules/`, cache, log, storage runtime, database SQLite, dan build asset hasil kompilasi.
- Data produksi atau data runtime yang dapat berubah.

## Catatan checkpoint

- Environment yang tersedia adalah local UAT; staging/deployment belum dilakukan.
- Jangan menganggap file ZIP ini sebagai release production atau pengganti Git repository.
- Status dan histori bisnis harus diaudit dari migration, model, service, controller, route, view, dan tests yang disertakan.
