# Panduan Codex Hemat Kuota — Untuk Pemilik SISTEM IMTAQ

## Prinsip utama
Bundle lengkap tetap disimpan. Codex **tidak perlu membaca semuanya** setiap kali bekerja.

Setiap sesi normal cukup dimulai dari:
`AGENTS.md → codex/CURRENT_TASK_CONTEXT.md → NEXT_ACTION.md → REQUIRED NOW`.

## Model yang dipilih bila tersedia
- **Terra**: default coding.
- **Luna**: pekerjaan ringan/rutin.
- **Sol**: hanya masalah yang benar-benar sulit.

Jika menu Codex Anda tidak menawarkan model tersebut, gunakan model coding yang tersedia; jangan mengubah arsitektur hanya karena pilihan model berbeda.

## Cara memulai
Gunakan `handoff/01_PROMPTS/21_QUOTA_EFFICIENT_START.txt`.

## Cara melanjutkan checkpoint
Pilih nomor checkpoint yang diberikan Codex. Untuk sesi normal, pilih checkpoint recommended yang menghasilkan satu hasil engineering utuh. Pilih quick hanya jika waktu Anda memang sempit.

## Setelah task selesai
Lebih baik membuka thread Codex baru untuk task berikutnya daripada membawa satu percakapan sangat panjang. Repository menyimpan state proyek.

## Jangan lakukan
- jangan paste seluruh percakapan lama;
- jangan minta "baca semua file dulu";
- jangan minta Codex menjelaskan ulang seluruh sistem setiap checkpoint;
- jangan memakai Sol terus-menerus hanya karena proyek penting;
- jangan mengorbankan test/security demi hemat kuota.

## Bila kuota hampir habis
Minta `SAFE CHECKPOINT NOW`. Setelah `SAFE_TO_CLOSE = YES`, hentikan sesi. Progress tetap berada di repository/Git dan dapat dilanjutkan setelah allowance/reset/credits tersedia.
