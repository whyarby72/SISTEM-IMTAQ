<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk ke Sistem IMTAQ</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#17352b;background:#f4f8f6}
        *{box-sizing:border-box}
        body{min-height:100vh;margin:0;display:grid;place-items:center;padding:1.25rem;background:linear-gradient(145deg,#f8fbf9 0%,#eef6f1 100%)}
        .auth-page{width:100%;max-width:29rem}
        .auth-card{padding:clamp(2.1rem,5vw,2.5rem) clamp(2rem,5vw,2.5rem);border:1px solid #d7e8df;border-radius:1.25rem;background:#fff;box-shadow:0 18px 48px rgba(26,74,54,.1)}
        .auth-logo{display:block;width:clamp(5.5rem,20vw,8.5rem);height:auto;margin:0 auto 1.1rem;object-fit:contain}
        .auth-heading{text-align:center;margin-bottom:1.5rem}
        .auth-title{margin:0;color:#124b37;font-size:clamp(1.65rem,4vw,2.1rem);font-weight:850;letter-spacing:-.045em}
        .auth-subtitle{margin:.45rem 0 0;color:#61776d;font-size:1rem}
        .auth-error{margin-bottom:1rem;padding:.75rem .85rem;border:1px solid #edc5b8;border-radius:.65rem;background:#fff5f1;color:#9a3f22;font-size:.9rem;line-height:1.4}
        .auth-form{display:grid;gap:.8rem}
        .auth-field{display:grid;gap:.4rem;color:#315b4d;font-size:.88rem;font-weight:800}
        .auth-input{width:100%;height:3rem;padding:0 .85rem;border:1px solid #bcd2c5;border-radius:.7rem;background:#fff;color:#17352b;font:inherit;font-size:1rem}
        .auth-input:hover{border-color:#8ebaa1}
        .auth-input:focus-visible{outline:3px solid #c8ead6;outline-offset:2px;border-color:#176b4d}
        .auth-password-control{position:relative}.auth-password-control .auth-input{padding-right:6.2rem}.auth-password-toggle{position:absolute;top:50%;right:.45rem;min-height:2.75rem;padding:.35rem .55rem;transform:translateY(-50%);border:0;border-radius:.5rem;background:#edf8f1;color:#176b4d;font:inherit;font-size:.78rem;font-weight:800;cursor:pointer}.auth-password-toggle:hover{background:#dff2e7}.auth-password-toggle:focus-visible{outline:3px solid #8bc9a6;outline-offset:1px}
        .auth-input:-webkit-autofill{box-shadow:0 0 0 1000px #fff inset;-webkit-text-fill-color:#17352b}
        .auth-remember{display:flex;align-items:center;gap:.6rem;margin:.1rem 0 .2rem;color:#315b4d;font-weight:700;cursor:pointer;min-height:2.5rem}
        .auth-remember input{width:1.15rem;height:1.15rem;margin:0;accent-color:#176b4d}
        .auth-remember input:focus-visible{outline:3px solid #c8ead6;outline-offset:2px}
        .auth-submit{width:100%;min-height:3rem;border:0;border-radius:.7rem;background:#176b4d;color:#fff;font:inherit;font-weight:850;cursor:pointer;transition:background .15s ease,transform .15s ease,box-shadow .15s ease}
        .auth-submit:hover{background:#0d573a;transform:translateY(-1px);box-shadow:0 7px 16px rgba(7,85,63,.18)}
        .auth-submit:disabled{background:#2f7b60;color:#eef8f2;cursor:wait;opacity:.9;transform:none;box-shadow:none}
        .auth-submit:focus-visible{outline:3px solid #8bc9a6;outline-offset:3px}
        .auth-submit:active{transform:translateY(0)}
        @media(max-width:480px){body{padding:.9rem}.auth-card{padding:1.55rem 1.1rem;border-radius:1rem;box-shadow:0 12px 28px rgba(26,74,54,.08)}.auth-logo{width:6.25rem;margin-bottom:.9rem}.auth-heading{margin-bottom:1.35rem}}
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-card" aria-labelledby="auth-title">
            <img src="{{ asset('images/logo-imtaq.png') }}" alt="IMTAQ Isy Karima" class="auth-logo">
            <div class="auth-heading">
                <h1 id="auth-title" class="auth-title">SISTEM IMTAQ</h1>
                <p class="auth-subtitle">Masuk ke akun Anda</p>
            </div>
            @if ($errors->any())<div class="auth-error" role="alert">{{ $errors->first() }}</div>@endif
            <form class="auth-form" method="POST" action="{{ route('login.store') }}" data-login-form>
                @csrf
                <label class="auth-field" for="email">Alamat email<input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
                <label class="auth-field" for="password">Kata sandi<div class="auth-password-control"><input class="auth-input" id="password" name="password" type="password" autocomplete="current-password" data-password-input required><button class="auth-password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi">Tampilkan</button></div></label>
                <label class="auth-remember" for="remember"><input id="remember" name="remember" type="checkbox" value="1"> <span>Ingat saya</span></label>
                <button class="auth-submit" type="submit" data-login-submit>Masuk</button>
            </form>
        </section>
    </main>
</body>
<script>
    const passwordInput = document.querySelector('[data-password-input]');
    const passwordToggle = document.querySelector('[data-password-toggle]');
    if (passwordInput && passwordToggle) {
        passwordToggle.addEventListener('click', function () {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            passwordToggle.textContent = isVisible ? 'Tampilkan' : 'Sembunyikan';
            passwordToggle.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        });
    }

    const loginForm = document.querySelector('[data-login-form]');
    const loginSubmit = document.querySelector('[data-login-submit]');
    if (loginForm && loginSubmit) {
        loginForm.addEventListener('submit', function () {
            loginSubmit.disabled = true;
            loginSubmit.textContent = 'Memproses...';
            loginSubmit.setAttribute('aria-busy', 'true');
        });
    }
</script>
</html>
