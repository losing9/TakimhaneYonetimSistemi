<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a0f1e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Takımhane YS') — 🔧 Takımhane</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0a0f1e;
            --surface:   #131929;
            --surface2:  #1a2235;
            --surface3:  #202b40;
            --border:    #263347;
            --primary:   #f97316;
            --primary-d: #ea6c0d;
            --primary-g: linear-gradient(135deg,#f97316,#dc4f00);
            --success:   #22c55e;
            --danger:    #ef4444;
            --warn:      #f59e0b;
            --info:      #38bdf8;
            --text:      #e8edf5;
            --muted:     #8896aa;
            --radius:    16px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Layout ─── */
        .app-shell   { display: flex; flex-direction: column; min-height: 100svh; }
        .app-content { flex: 1; padding: 0 0 88px; overflow-x: hidden; }

        /* ─── Header ─── */
        .app-header {
            position: sticky; top: 0; z-index: 50;
            background: rgba(10,15,30,.88);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255,255,255,.06);
            padding: 12px 16px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .app-header .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .app-header .logo-icon {
            width: 38px; height: 38px;
            background: var(--primary-g);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 16px rgba(249,115,22,.35);
        }
        .app-header .logo-text { font-weight: 800; font-size: 15px; color: var(--text); line-height: 1.2; }
        .app-header .logo-sub  { font-size: 10px; color: var(--muted); letter-spacing: .04em; }
        .header-user { display: flex; align-items: center; gap: 8px; }
        .header-user .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--primary-g);
            color: #fff; font-weight: 800; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 10px rgba(249,115,22,.3);
        }
        .logout-btn {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 10px; cursor: pointer; padding: 7px;
            color: var(--muted); display: flex; align-items: center; justify-content: center;
            transition: all .2s;
        }
        .logout-btn:hover { color: var(--danger); border-color: var(--danger); }

        /* ─── Bottom Nav ─── */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            background: rgba(13,19,35,.96);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-top: 1px solid rgba(255,255,255,.06);
            display: flex;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .nav-item {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            padding: 10px 4px 8px; text-decoration: none; color: var(--muted);
            font-size: 10px; font-weight: 600; gap: 4px;
            transition: color .2s; -webkit-tap-highlight-color: transparent;
            position: relative;
        }
        .nav-item svg { width: 22px; height: 22px; }
        .nav-item.active { color: var(--primary); }
        .nav-item.active::before {
            content: '';
            position: absolute; top: 0; left: 50%;
            transform: translateX(-50%);
            width: 28px; height: 3px;
            background: var(--primary);
            border-radius: 0 0 3px 3px;
        }
        .nav-item-scan {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            text-decoration: none; color: #fff; gap: 4px; font-size: 10px; font-weight: 700;
            padding: 6px 4px 8px; -webkit-tap-highlight-color: transparent;
        }
        .nav-item-scan .scan-btn {
            width: 54px; height: 54px; border-radius: 18px;
            background: var(--primary-g);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 24px rgba(249,115,22,.5);
            margin-top: -22px;
            transition: transform .15s;
        }
        .nav-item-scan:active .scan-btn { transform: scale(.92); }
        .nav-item-scan svg { width: 26px; height: 26px; }

        /* ─── Cards ─── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px; margin: 10px 16px;
        }
        .card-title {
            font-size: 11px; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: .07em; margin-bottom: 12px;
            display: flex; align-items: center; justify-content: space-between;
        }

        /* ─── Buttons ─── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 20px; border-radius: 14px; border: none;
            font-size: 15px; font-weight: 700; cursor: pointer; text-decoration: none;
            transition: all .2s; width: 100%; -webkit-tap-highlight-color: transparent;
            letter-spacing: -.01em;
        }
        .btn:active { transform: scale(.97); }
        .btn-primary { background: var(--primary-g); color: #fff; box-shadow: 0 4px 16px rgba(249,115,22,.3); }
        .btn-success { background: linear-gradient(135deg,#22c55e,#16a34a); color: #fff; box-shadow: 0 4px 16px rgba(34,197,94,.25); }
        .btn-danger  { background: linear-gradient(135deg,#ef4444,#dc2626); color: #fff; }
        .btn-ghost   { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-sm { padding: 9px 14px; font-size: 13px; border-radius: 10px; width: auto; }

        /* ─── Alert ─── */
        .alert {
            padding: 13px 16px; border-radius: 12px;
            font-size: 14px; margin: 8px 16px;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .alert-danger  { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.25);  color: #fca5a5; }
        .alert-success { background: rgba(34,197,94,.1);  border: 1px solid rgba(34,197,94,.25);  color: #86efac; }
        .alert-warn    { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.25); color: #fcd34d; }

        /* ─── Loan Items ─── */
        .loan-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 0; border-bottom: 1px solid var(--border);
        }
        .loan-item:last-child { border-bottom: none; padding-bottom: 0; }
        .loan-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: var(--surface2);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .loan-info { flex: 1; min-width: 0; }
        .loan-name { font-size: 14px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .loan-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 700; }
        .badge-active  { background: rgba(34,197,94,.15); color: #4ade80; }
        .badge-overdue { background: rgba(239,68,68,.15); color: #f87171; }

        /* ─── Form ─── */
        .form-group { margin-bottom: 14px; }
        .form-label { font-size: 13px; font-weight: 600; color: var(--muted); display: block; margin-bottom: 7px; }
        .form-input {
            width: 100%; padding: 13px 15px;
            background: var(--surface2); border: 1.5px solid var(--border);
            border-radius: 12px; color: var(--text); font-size: 15px; outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(249,115,22,.12); }
        .form-select { appearance: none; }

        /* ─── QR Scanner ─── */
        #qr-video-container,
        #qr-video-container-1,
        #qr-video-container-2 {
            position: relative; width: 100%;
            aspect-ratio: 1; background: #000;
            border-radius: 16px; overflow: hidden; margin-top: 10px;
        }
        #qr-video, #qr-video-1, #qr-video-2 { width: 100%; height: 100%; object-fit: cover; }
        .qr-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        .qr-frame {
            width: 200px; height: 200px;
            border: 3px solid var(--primary);
            border-radius: 20px;
            box-shadow: 0 0 0 9999px rgba(0,0,0,.55), 0 0 24px rgba(249,115,22,.3);
        }
        .qr-scan-line {
            position: absolute; width: 180px; height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            animation: scan 2s ease-in-out infinite;
        }
        @keyframes scan { 0%,100% { top: calc(50% - 95px); } 50% { top: calc(50% + 95px); } }

        /* ─── Utility ─── */
        .text-center { text-align: center; }
        .text-muted  { color: var(--muted); font-size: 13px; }
        .mt-1 { margin-top: 6px; }  .mt-2 { margin-top: 12px; }
        .mt-3 { margin-top: 18px; } .mt-4 { margin-top: 24px; }
        .px-4 { padding-left: 16px; padding-right: 16px; }
        .hidden { display: none !important; }
        .spinner { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,.25); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ─── Lightbox ─── */
        .lightbox-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            z-index: 9999;
            display: none;          /* JS .open class'ı ile flex yapılır */
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .lightbox-overlay.open { display: flex; }
        .lightbox-inner {
            position: relative;
            display: flex; flex-direction: column; align-items: center;
            max-width: 94vw;
        }
        .lightbox-close {
            position: absolute; top: -14px; right: -14px;
            background: var(--danger); color: #fff;
            border: none; width: 36px; height: 36px;
            border-radius: 50%; font-size: 18px; font-weight: 800;
            cursor: pointer; z-index: 10;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,.6);
            transition: transform .15s;
        }
        .lightbox-close:active { transform: scale(.9); }
        .lightbox-img {
            max-width: 94vw; max-height: 82vh;
            object-fit: contain;
            border-radius: 14px;
            border: 1.5px solid rgba(255,255,255,.12);
            box-shadow: 0 32px 80px rgba(0,0,0,.85);
        }
        .lightbox-caption {
            margin-top: 12px; color: #fff; font-size: 14px; font-weight: 600;
            background: rgba(10,15,30,.85); padding: 6px 18px;
            border-radius: 20px; border: 1px solid rgba(255,255,255,.1);
            text-align: center;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">
    {{-- Header --}}
    <header class="app-header">
        <a href="{{ route('portal.index') }}" class="logo">
            <div class="logo-icon">🔧</div>
            <div>
                <div class="logo-text">Takımhane</div>
                <div class="logo-sub">YÖNETİM SİSTEMİ</div>
            </div>
        </a>
        <div class="header-user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0">
                @csrf
                <button type="submit" class="logout-btn" title="Çıkış">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    {{-- Flash Messages --}}
    <div class="app-content">
        @if(session('success'))
            <div class="alert alert-success mt-2">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mt-2">⚠️ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger mt-2">
                <ul style="list-style:none">
                    @foreach($errors->all() as $e) <li>⚠️ {{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    {{-- Bottom Navigation --}}
    <nav class="bottom-nav">
        <a href="{{ route('portal.index') }}" class="nav-item {{ request()->routeIs('portal.index') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Ana Sayfa
        </a>
        <a href="{{ route('portal.my-loans') }}" class="nav-item {{ request()->routeIs('portal.my-loans') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Zimmetlerim
        </a>
        <a href="{{ route('portal.scan') }}" class="nav-item-scan">
            <div class="scan-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </div>
            QR Tara
        </a>
        <a href="{{ route('portal.return') }}" class="nav-item {{ request()->routeIs('portal.return') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.13"/>
            </svg>
            İade Et
        </a>
        <a href="{{ route('portal.profile') }}" class="nav-item {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Profil
        </a>
    </nav>
</div>

{{-- ─── LIGHTBOX MODAL ─────────────────────────────────────────────────────────
     ÖNEMLI: inline "display:flex" YOK — sadece .open class'ı toggle edilir.
     Böylece .hidden ile çakışma olmaz ve fotoğraflara tıklanınca çalışır.
     ─────────────────────────────────────────────────────────────────────── --}}
<div id="image-lightbox-modal"
     class="lightbox-overlay"
     onclick="closeImageLightbox(event)">
    <div class="lightbox-inner" onclick="event.stopPropagation()">
        <button class="lightbox-close" onclick="closeImageLightbox()">✕</button>
        <img id="lightbox-img" src="" alt="Resim Önizleme" class="lightbox-img">
        <div id="lightbox-caption" class="lightbox-caption" style="display:none;"></div>
    </div>
</div>

<script>
function openImageLightbox(src, caption) {
    var modal = document.getElementById('image-lightbox-modal');
    var img   = document.getElementById('lightbox-img');
    var cap   = document.getElementById('lightbox-caption');
    if (!modal || !img) return;
    img.src = src;
    if (caption) {
        cap.textContent = caption;
        cap.style.display = '';
    } else {
        cap.style.display = 'none';
    }
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeImageLightbox(e) {
    var modal = document.getElementById('image-lightbox-modal');
    if (modal) modal.classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeImageLightbox();
});

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
}
</script>
@stack('scripts')
</body>
</html>
