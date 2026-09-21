<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIM Kesekretariatan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #0f172a; color: #0f172a; display: flex; align-items: center; justify-content: center; padding: 24px; }
        a { text-decoration: none; }

        .auth-card { background: #fff; border: 1px solid #b8d6f5; border-top: 4px solid #2563eb; border-radius: 28px; padding: 40px 36px; width: 100%; max-width: 400px; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }
        .auth-logo { width: 48px; height: 48px; border-radius: 14px; background: #172033; border: 3px solid #8bcfc5; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin: 0 auto 16px; }
        .auth-title { text-align: center; margin: 0 0 4px; font-size: 1.3rem; font-weight: 800; }
        .auth-subtitle { text-align: center; margin: 0 0 28px; color: #64748b; font-size: 0.9rem; }

        .form-label { display: block; margin-bottom: 8px; color: #334155; font-size: 0.9rem; font-weight: 600; }
        .form-control { width: 100%; border: 1px solid #8bcfc5; border-radius: 16px; padding: 13px 14px; background: #fbfffe; color: #0f172a; outline: none; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .form-control:focus { border-color: #0f766e; box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.14); }
        .field-group { margin-bottom: 18px; }

        .password-wrap { position: relative; }
        .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; font-size: 1.05rem; padding: 4px; }

        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; font-size: 0.88rem; }
        .remember-row label { display: flex; align-items: center; gap: 8px; color: #475569; cursor: pointer; }
        .remember-row a { color: #0f172a; font-weight: 600; }

        .button { width: 100%; border: none; border-radius: 999px; padding: 14px; cursor: pointer; font-weight: 700; font-size: 0.95rem; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; box-shadow: 0 14px 28px rgba(15, 23, 42, 0.15); transition: transform 0.18s ease; }
        .button:hover { transform: translateY(-1px); }

        .alert { border-radius: 16px; padding: 12px 14px; border: 1px solid transparent; font-size: 0.88rem; margin-bottom: 18px; }
        .alert-error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .alert-error ul { margin: 0; padding-left: 18px; }

        .back-home { display: block; text-align: center; margin-top: 20px; color: #94a3b8; font-size: 0.85rem; }
    </style>
</head>
<body>

    <div class="auth-card">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 56px; display: block; margin: 0 auto 16px;">
        <h1 class="auth-title">Masuk ke Sistem</h1>
        <p class="auth-subtitle">Sistem Informasi Sekretariat & Pengarsipan</p>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            </div>

            <div class="field-group">
                <label class="form-label">Kata Sandi</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password-input" class="form-control" style="padding-right: 42px;" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                </div>
            </div>

            <div class="remember-row" style="justify-content: flex-start;">
                <label>
                    <input type="checkbox" name="remember" style="accent-color:#0f172a;">
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="button">Masuk</button>
        </form>

        <a href="{{ url('/') }}" class="back-home">&larr; Kembali ke beranda</a>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password-input');
            const btn = document.querySelector('.password-toggle');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>