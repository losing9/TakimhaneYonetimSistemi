<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Yönetici Portalı') — 🔧 Takımhane</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-title" content="Takımhane">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0f172a;
            --surface:   #1e293b;
            --surface2:  #243044;
            --surface3:  #2e3d55;
            --border:    #334155;
            --border-sub:#1e293b;
            --primary:   #4f46e5;
            --primary-d: #4338ca;
            --success:   #10b981;
            --danger:    #ef4444;
            --warn:      #f59e0b;
            --text:      #f8fafc;
            --text-sub:  #cbd5e1;
            --muted:     #94a3b8;
            --radius:    14px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; background: var(--bg); color: var(--text); font-family: 'Inter', -apple-system, sans-serif; -webkit-font-smoothing: antialiased; }

        /* ─── Layout ─── */
        .app-shell { display: flex; flex-direction: column; min-height: 100svh; max-width: 1200px; margin: 0 auto; }
        .app-content { flex: 1; padding: 0 0 90px; overflow-x: hidden; }

        /* ─── Header ─── */
        .app-header {
            position: sticky; top: 0; z-index: 50;
            background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 12px 18px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .app-header .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .app-header .logo-icon { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), var(--primary-d)); border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
        .app-header .logo-text { font-weight: 700; font-size: 16px; color: #fff; line-height: 1.2; }
        .app-header .logo-sub  { font-size: 11px; color: var(--muted); }
        .header-user { display: flex; align-items: center; gap: 10px; }
        .header-user .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-d)); color: #fff; font-weight: 700; font-size: 14px;
            display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.1);
        }

        /* ─── Bottom Nav ─── */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            background: rgba(30, 41, 59, 0.97); backdrop-filter: blur(20px);
            border-top: 1px solid var(--border);
            display: flex; safe-area-inset-bottom: env(safe-area-inset-bottom);
            padding-bottom: env(safe-area-inset-bottom);
            max-width: 1200px; margin: 0 auto;
        }
        .nav-item {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            padding: 10px 4px 8px; text-decoration: none; color: var(--muted);
            font-size: 11px; font-weight: 600; gap: 4px; transition: all .2s;
        }
        .nav-item svg { width: 22px; height: 22px; }
        .nav-item.active { color: #818cf8; }
        .nav-item:hover { color: #fff; }
        .nav-item-scan {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            text-decoration: none; color: #fff; gap: 4px; font-size: 11px; font-weight: 700;
            padding: 6px 4px 8px;
        }
        .nav-item-scan .scan-btn {
            width: 52px; height: 52px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-d));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(79,70,229,.6);
            margin-top: -22px; border: 3px solid var(--bg); transition: transform .2s;
        }
        .nav-item-scan .scan-btn:hover { transform: scale(1.05); }
        .nav-item-scan svg { width: 26px; height: 26px; }

        /* ─── Cards ─── */
        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 18px; margin: 12px 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        .card-title { font-size: 13px; font-weight: 700; color: #cbd5e1; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 12px; }

        /* ─── Buttons ─── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 18px; border-radius: 10px; border: 1px solid transparent;
            font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none;
            transition: all .2s; -webkit-tap-highlight-color: transparent; white-space: nowrap;
        }
        .btn:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn:active { transform: scale(0.98); }
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,0.35); }
        .btn-success { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,0.35); }
        .btn-danger  { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 4px 12px rgba(239,68,68,0.35); }
        .btn-warn    { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 4px 12px rgba(245,158,11,0.35); }
        .btn-ghost   { background: var(--surface2); color: #fff; border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--surface3); border-color: #475569; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }
        .btn-xs { padding: 4px 8px; font-size: 11px; border-radius: 6px; }

        /* ─── Badges ─── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;
        }
        .badge-active  { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .badge-overdue { background: rgba(239,68,68,0.18); color: #f87171; border: 1px solid rgba(239,68,68,0.35); }
        .badge-warn    { background: rgba(245,158,11,0.18); color: #fbbf24; border: 1px solid rgba(245,158,11,0.35); }

        /* ─── Alert ─── */
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin: 0 16px 12px; display: flex; gap: 8px; align-items: flex-start; }
        .alert-danger  { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.35); color: #fca5a5; }
        .alert-success { background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.35); color: #a7f3d0; }
        .alert-warn    { background: rgba(245,158,11,.15); border: 1px solid rgba(245,158,11,.35); color: #fcd34d; }

        /* ─── Form ─── */
        .form-group { margin-bottom: 14px; }
        .form-label { font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 6px; }
        .form-input {
            width: 100%; padding: 10px 14px; background: var(--surface2); border: 1px solid var(--border);
            border-radius: 10px; color: #fff; font-size: 14px; outline: none;
            transition: all .2s;
        }
        .form-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }

        /* ─── Utility ─── */
        .text-center { text-align: center; }
        .text-muted { color: var(--muted); font-size: 13px; }
        .hidden { display: none !important; }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">
    {{-- Header --}}
    <header class="app-header">
        <a href="{{ route('admin-portal.index') }}" class="logo">
            <div class="logo-icon">🔧</div>
            <div>
                <div class="logo-text">Takımhane</div>
                <div class="logo-sub">Yönetici Paneli</div>
            </div>
        </a>
        <div class="header-user">
            <div class="avatar">{{ strtoupper(substr(Auth::user()?->name ?? 'A', 0, 1)) }}</div>
            @if(Auth::check())
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-ghost btn-xs" style="color:#f87171; border-color:rgba(239,68,68,0.3);" title="Çıkış Yap">
                    Çıkış
                </button>
            </form>
            @endif
        </div>
    </header>

    {{-- Ana İçerik --}}
    <main class="app-content">
        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    <nav class="bottom-nav">
        <a href="{{ route('admin-portal.index') }}" class="nav-item {{ request()->routeIs('admin-portal.index') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Genel Bakış</span>
        </a>

        <a href="{{ route('admin-portal.add-tool') }}" class="nav-item-scan">
            <div class="scan-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span>Alet Ekle</span>
        </a>

        <a href="/admin" class="nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Filament</span>
        </a>
    </nav>
</div>

{{-- GLOBAL RESİM LIGHTBOX --}}
<style>
.lb-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.92);
    backdrop-filter: blur(14px);
    z-index: 9999;
    display: none;           /* .open ile flex yapılır — .hidden çakışması yok */
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.lb-overlay.open { display: flex; }
.lb-inner {
    position: relative;
    display: flex; flex-direction: column; align-items: center;
    max-width: 92vw;
}
.lb-close {
    position: absolute; top: -14px; right: -14px;
    background: #ef4444; color: #fff;
    border: none; width: 36px; height: 36px;
    border-radius: 50%; font-size: 18px; font-weight: 800;
    cursor: pointer; z-index: 10;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(0,0,0,.6);
}
.lb-img {
    max-width: 90vw; max-height: 82vh;
    object-fit: contain; border-radius: 12px;
    border: 1.5px solid rgba(255,255,255,.15);
    box-shadow: 0 24px 60px rgba(0,0,0,.85);
}
.lb-cap {
    margin-top: 10px; color: #fff; font-size: 14px; font-weight: 600;
    background: rgba(15,23,42,.85); padding: 5px 16px;
    border-radius: 20px; border: 1px solid rgba(255,255,255,.1);
    text-align: center;
}
</style>

<div id="image-lightbox-modal"
     class="lb-overlay"
     onclick="closeImageLightbox(event)">
    <div class="lb-inner" onclick="event.stopPropagation()">
        <button class="lb-close" onclick="closeImageLightbox()">✕</button>
        <img id="lightbox-img" src="" alt="Resim" class="lb-img">
        <div id="lightbox-caption" class="lb-cap" style="display:none;"></div>
    </div>
</div>

<script>
function openImageLightbox(src, caption) {
    var modal = document.getElementById('image-lightbox-modal');
    var img   = document.getElementById('lightbox-img');
    var cap   = document.getElementById('lightbox-caption');
    if (!modal || !img) return;
    img.src = src;
    if (caption) { cap.textContent = caption; cap.style.display = ''; }
    else { cap.style.display = 'none'; }
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
</script>

@stack('scripts')
</body>
</html>
