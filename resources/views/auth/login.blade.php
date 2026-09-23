<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Giriş — Takımhane YS</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:      #0f172a;
            --surface: #1e293b;
            --border:  #334155;
            --primary: #f97316;
            --text:    #f1f5f9;
            --muted:   #94a3b8;
        }
        html, body {
            height: 100%; background: var(--bg);
            font-family: 'Inter', sans-serif; color: var(--text);
            overflow-x: hidden;
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image: radial-gradient(circle at 30% 20%, rgba(249,115,22,.12) 0%, transparent 50%),
                              radial-gradient(circle at 80% 80%, rgba(99,102,241,.10) 0%, transparent 50%);
        }
        .page {
            position: relative; z-index: 1;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-height: 100svh; padding: 32px 24px;
        }

        /* ─── Logo ─── */
        .logo { text-align: center; margin-bottom: 40px; }
        .logo-icon {
            width: 72px; height: 72px; border-radius: 22px; margin: 0 auto 16px;
            background: linear-gradient(135deg, #f97316, #ea6c0d);
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; box-shadow: 0 8px 32px rgba(249,115,22,.35);
        }
        .logo h1 { font-size: 26px; font-weight: 800; letter-spacing: -.5px; }
        .logo p  { font-size: 14px; color: var(--muted); margin-top: 4px; }

        /* ─── Card ─── */
        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 20px; padding: 28px 24px; width: 100%; max-width: 400px;
            box-shadow: 0 24px 60px rgba(0,0,0,.5);
        }
        .card-title { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .card-sub   { font-size: 14px; color: var(--muted); margin-bottom: 24px; }

        /* ─── Form ─── */
        .form-group { margin-bottom: 16px; }
        .form-label { font-size: 13px; font-weight: 500; color: var(--muted); display: block; margin-bottom: 6px; }
        .form-input {
            width: 100%; padding: 14px 16px;
            background: #0f172a; border: 1.5px solid var(--border);
            border-radius: 12px; color: var(--text); font-size: 16px;
            outline: none; transition: border-color .2s;
        }
        .form-input:focus { border-color: var(--primary); }
        .form-error { font-size: 12px; color: #f87171; margin-top: 4px; }

        .btn-login {
            width: 100%; padding: 15px; border-radius: 12px; border: none;
            background: linear-gradient(135deg, #f97316, #ea6c0d);
            color: #fff; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: all .2s; margin-top: 4px;
            box-shadow: 0 4px 20px rgba(249,115,22,.4);
        }
        .btn-login:hover  { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(249,115,22,.5); }
        .btn-login:active { transform: translateY(0); }

        .checkbox-row { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--muted); }
        .checkbox-row input { accent-color: var(--primary); width: 16px; height: 16px; }

        .alert {
            padding: 12px 14px; border-radius: 10px; font-size: 13px;
            margin-bottom: 16px; display: flex; gap: 8px; align-items: flex-start;
        }
        .alert-danger { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25); color: #fca5a5; }

        .footer-text { text-align: center; font-size: 12px; color: var(--muted); margin-top: 24px; }

        /* ─── Feature Pills ─── */
        .features { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 32px; }
        .feature-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 99px;
            background: rgba(249,115,22,.08); border: 1px solid rgba(249,115,22,.2);
            font-size: 12px; color: #fdba74;
        }
    </style>
</head>
<body>
<div class="bg-grid"></div>
<div class="page">
    {{-- Logo --}}
    <div class="logo">
        <div class="logo-icon">🔧</div>
        <h1>Takımhane YS</h1>
        <p>Takım & Alet Yönetim Sistemi</p>
    </div>

    {{-- Feature pills --}}
    <div class="features">
        <span class="feature-pill">📷 QR Zimmet</span>
        <span class="feature-pill">📊 Raporlama</span>
        <span class="feature-pill">🔐 Güvenli İade</span>
    </div>

    {{-- Login Card --}}
    <div class="card">
        <div class="card-title">Hoş Geldiniz</div>
        <div class="card-sub">Hesabınıza giriş yapın</div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $err) <div>⚠️ {{ $err }}</div> @endforeach
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">E-posta Adresi</label>
                <input class="form-input" type="email" id="email" name="email"
                       value="{{ old('email') }}" placeholder="ornek@sirket.com"
                       autocomplete="email" autofocus required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Şifre</label>
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>

            <div class="form-group checkbox-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Beni hatırla</label>
            </div>

            <button type="submit" class="btn-login">Giriş Yap →</button>
        </form>
    </div>

    <div class="footer-text">
        Takımhane Yönetim Sistemi &copy; {{ date('Y') }}<br>
        Admin erişimi için yöneticinizle iletişime geçin.
    </div>
</div>

<script>
if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(() => {});
</script>
</body>
</html>
