<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Masuk ke Sistem IMTAQ</title><style>@include('admin.partials.styles') .login-form>button{margin-top:.75rem}</style></head>
<body><main style="max-width:28rem;margin:4rem auto"><section class="card"><p class="eyebrow">Akses Sistem IMTAQ</p><h1>Masuk ke Sistem IMTAQ</h1><p class="muted">Gunakan akun yang diberikan pengelola sistem.</p>
@if ($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
<form class="login-form" method="POST" action="{{ route('login.store') }}">@csrf
<label for="email">Alamat email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
<label for="password">Kata sandi</label><input id="password" name="password" type="password" required>
<label><input name="remember" type="checkbox" value="1" style="width:auto;margin-right:.4rem"> Ingat saya</label>
<button class="button" type="submit">Masuk</button></form></section></main></body></html>
