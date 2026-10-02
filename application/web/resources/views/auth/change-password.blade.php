<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ganti password</title></head>
<body style="font-family:system-ui;background:#f3f8f5;color:#173d33;padding:2rem">
<main style="max-width:32rem;margin:4rem auto;background:#fff;border:1px solid #cfe4d9;border-radius:1rem;padding:2rem">
<h1>Ganti password</h1><p>Password sementara harus diganti sebelum melanjutkan.</p>
@if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
<form method="post" action="{{ route('password.update') }}">@csrf @method('PUT')
<p><label for="current_password">Password saat ini</label><br><input id="current_password" name="current_password" type="password" required autocomplete="current-password"></p>
<p><label for="password">Password baru</label><br><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password"></p>
<p><label for="password_confirmation">Ulangi password baru</label><br><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password"></p>
<button type="submit">Simpan password</button></form>
</main></body></html>
