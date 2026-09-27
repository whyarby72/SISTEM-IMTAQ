# Panduan Memilih Checkpoint Codex

Anda tidak perlu mengetahui berapa lama seluruh task akan selesai. Setelah satu langkah aman selesai, Codex harus menawarkan pilihan checkpoint berikutnya beserta estimasi waktunya.

Contoh:

```text
1. STANDARD — ±15–25 menit (RECOMMENDED)
   Selesaikan setup Laravel + smoke test.

2. QUICK — ±5–10 menit
   Hanya selesaikan struktur folder/config yang aman.

3. EXTENDED — ±35–50 menit
   Setup + dependency + test bootstrap bila berjalan normal.

4. STOP NOW
   SAFE_TO_CLOSE = YES
```

Cukup jawab nomor yang Anda pilih.

## Arti estimasi
Estimasi adalah **kisaran**, bukan janji. Download dependency, internet, build, test besar, atau masalah environment dapat membuat proses lebih lama.

Jika Codex tidak bisa menawarkan checkpoint 5 menit dengan aman, Codex harus mengatakan bahwa checkpoint terpendek lebih lama. Jangan memaksa pilihan 5 menit jika operasi teknisnya tidak aman dipotong.

## Kapan MacBook aman ditutup?
Cari baris:

```text
SAFE_TO_CLOSE = YES
```

Jika tertulis `NO`, lihat alasannya dan jangan tutup MacBook sampai operasi lokal yang aktif selesai/recover.

## Jika harus pergi mendadak
Ketik:

```text
SAFE CHECKPOINT NOW
```

Codex harus berhenti memulai pekerjaan baru, menyelesaikan hanya operasi aktif yang perlu diselesaikan dengan aman, menyimpan resume point, lalu menyatakan `SAFE_TO_CLOSE`.

## Jika kembali besok
Buka repository yang sama dan gunakan prompt `handoff/01_PROMPTS/03_RESUME_AFTER_INTERRUPTION.txt`. Codex harus membaca work log + Git status dan melanjutkan dari checkpoint, bukan membuat ulang pekerjaan.
