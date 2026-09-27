# SISTEM IMTAQ — Login UI Audit Bundle

Tanggal: 14 September 2026

Bundle ini disiapkan untuk audit dan redesign presentasi halaman `/login`.
Repository source tetap menjadi sumber kebenaran teknis. Fokus bundle adalah UI login dan konteks autentikasi lokal yang langsung terkait.

## Isi

- `application/web/resources/views/auth/login.blade.php` — halaman login aktif.
- `application/web/resources/views/admin/partials/styles.blade.php` — style partial yang di-include login.
- `application/web/app/Http/Controllers/Auth/AuthenticatedSessionController.php` — alur login/logout dan redirect role.
- `application/web/routes/web.php` — route login/logout dan route tujuan redirect.
- `application/web/tests/Feature/Auth/LocalAuthenticationTest.php` — regression contract autentikasi.
- `application/web/app/Models/User.php` — model user yang relevan.
- `codex/LOGIN_UI_AUDIT_PROMPT.txt` — prompt siap ditempel ke ChatGPT.
- `codex/CHANGE_MANIFESTS/IMP-AUTH-001-2026-09-04.md` dan `IMP-WAKA-UI-LOGIN-2026-09-08.md` — riwayat perubahan login.
- `codex/CURRENT_TASK_CONTEXT.md` dan `NEXT_ACTION.md` — status proyek saat ini.

## Keamanan

Tidak menyertakan `.env`, credential, secret, database dump, runtime cache, atau `vendor`.
Audit/reka tampilan tidak mengubah route, autentikasi, RBAC, atau database tanpa instruksi eksplisit.
