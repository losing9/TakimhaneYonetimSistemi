<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#11161f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toolroom Manager') — Takımhane Yönetim Sistemi</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-title" content="Takımhane">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:           #11161f;
            --bg-body:      #0e1219;
            --sidebar-bg:   #0b0e14;
            --header-bg:    #141923;
            --surface:      #18202c;
            --surface2:     #1f2837;
            --surface3:     #283548;
            --border:       #263346;
            --border-light: #32435b;
            --accent:       #f59e0b;
            --accent-hover: #d97706;
            --accent-soft:  rgba(245, 158, 11, 0.15);
            --primary:      #f59e0b;
            --success:      #10b981;
            --success-soft: rgba(16, 185, 129, 0.16);
            --info:         #3b82f6;
            --info-soft:    rgba(59, 130, 246, 0.16);
            --danger:       #ef4444;
            --danger-soft:  rgba(239, 68, 68, 0.18);
            --warn:         #f59e0b;
            --text:         #f8fafc;
            --text-sub:     #cbd5e1;
            --muted:        #8b9bb4;
            --radius-lg:    14px;
            --radius-md:    10px;
            --radius-sm:    6px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            background: var(--bg-body);
            color: var(--text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Modern App Shell ─── */
        .admin-shell {
            display: flex;
            min-height: 100vh;
            background: var(--bg-body);
            position: relative;
        }

        /* ─── Slim Left Sidebar ─── */
        .admin-sidebar {
            width: 68px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 60;
            flex-shrink: 0;
        }

        .sidebar-nav-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        .sidebar-btn {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            text-decoration: none;
            transition: all .2s ease;
            position: relative;
            background: transparent;
            border: none;
            cursor: pointer;
        }

        .sidebar-btn:hover {
            color: #ffffff;
            background: var(--surface);
        }

        .sidebar-btn.active {
            background: var(--accent);
            color: #0b0e14;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
        }

        .sidebar-btn svg {
            width: 22px;
            height: 22px;
        }

        /* ─── Main Content Wrapper ─── */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: var(--bg);
        }

        /* ─── Top Header ─── */
        .admin-topbar {
            height: 68px;
            background: var(--header-bg);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(12px);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-icon-box {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        }

        .brand-text-block {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .brand-title {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.8px;
            color: #f59e0b;
            text-transform: uppercase;
        }

        .brand-sub {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #ffffff;
            text-transform: uppercase;
        }

        /* Topbar Center: Search */
        .topbar-search-box {
            flex: 1;
            max-width: 520px;
            position: relative;
        }

        .topbar-search-input {
            width: 100%;
            height: 42px;
            background: #10151f;
            border: 1px solid var(--border);
            border-radius: 99px;
            padding: 0 16px 0 42px;
            color: #fff;
            font-size: 13px;
            outline: none;
            transition: all .2s;
        }

        .topbar-search-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
            background: #141b27;
        }

        .topbar-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
            width: 18px;
            height: 18px;
        }

        /* Topbar Right Actions */
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-action-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-sub);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all .2s;
            position: relative;
            cursor: pointer;
        }

        .topbar-action-icon:hover {
            color: #fff;
            border-color: var(--border-light);
            background: var(--surface2);
        }

        .topbar-action-icon .badge-dot {
            position: absolute;
            top: -3px;
            right: -3px;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--header-bg);
        }

        /* User Profile Pill */
        .user-profile-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 10px 4px 6px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 99px;
            cursor: pointer;
            transition: all .2s;
        }

        .user-profile-menu:hover {
            border-color: var(--border-light);
            background: var(--surface2);
        }

        .user-avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, #b45309);
            color: #000;
            font-weight: 800;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }

        .user-info-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
            text-align: left;
        }

        .user-name-title {
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        .user-role-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
        }

        /* ─── Body Content ─── */
        .admin-page-content {
            flex: 1;
            padding: 24px;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
        }

        /* ─── Universal Component Styles ─── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            position: relative;
        }

        .card-title {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #e2e8f0;
            text-transform: uppercase;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-accent {
            background: var(--accent);
            color: #0f141c;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
        }

        .btn-accent:hover {
            background: #fbbf24;
            color: #000;
        }

        .btn-success {
            background: #10b981;
            color: #fff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
        }

        .btn-ghost {
            background: var(--surface2);
            color: #e2e8f0;
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            background: var(--surface3);
            border-color: var(--border-light);
            color: #fff;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-xs {
            padding: 4px 8px;
            font-size: 11px;
            border-radius: 5px;
        }

        /* ─── Status Badges matching Mockup ─── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .status-available {
            background: #064e3b;
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .status-in-use {
            background: #1e3a8a;
            color: #60a5fa;
            border: 1px solid rgba(96, 165, 250, 0.3);
        }

        .status-overdue {
            background: #7f1d1d;
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.35);
        }

        .status-maintenance {
            background: #78350f;
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        /* Form elements */
        .form-group { margin-bottom: 14px; }
        .form-label { font-size: 12px; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 6px; }
        .form-input {
            width: 100%;
            padding: 10px 14px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            outline: none;
            transition: all .2s;
            font-family: inherit;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: var(--success-soft); border: 1px solid rgba(16, 185, 129, 0.35); color: #a7f3d0; }
        .alert-danger  { background: var(--danger-soft);  border: 1px solid rgba(239, 68, 68, 0.35);  color: #fca5a5; }
        .alert-warn    { background: var(--accent-soft);  border: 1px solid rgba(245, 158, 11, 0.35); color: #fcd34d; }

        .hidden { display: none !important; }

        /* Responsive */
        @media (max-width: 992px) {
            .admin-sidebar {
                display: none;
            }
            .admin-topbar {
                padding: 0 16px;
            }
            .admin-page-content {
                padding: 16px;
            }
            .user-info-text {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-shell">
    {{-- Slim Left Sidebar --}}
    <aside class="admin-sidebar">
        <div class="sidebar-nav-group">
            <button type="button" class="sidebar-btn" title="Menü">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <a href="{{ route('admin-portal.index') }}" class="sidebar-btn" title="Ana Sayfa">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>

            <a href="{{ route('admin-portal.index') }}" class="sidebar-btn active" title="Dashboard">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 4h7v7H4V4zm0 9h7v7H4v-7zm9-9h7v7h-7V4zm0 9h7v7h-7v-7z"/>
                </svg>
            </a>

            <a href="{{ route('admin-portal.index') }}" class="sidebar-btn" title="Zimmet Geçmişi & Günlük Takip">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </a>

            <a href="{{ route('admin-portal.add-tool') }}" class="sidebar-btn" title="Yeni Takım Ekle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </a>

            <a href="/admin" class="sidebar-btn" title="Filament Yönetim Paneli">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </div>

        <div class="sidebar-nav-group">
            @if(Auth::check())
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="sidebar-btn" title="Çıkış Yap">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
            @endif
        </div>
    </aside>

    {{-- Main Column --}}
    <div class="admin-main">
        {{-- Topbar --}}
        <header class="admin-topbar">
            <div class="topbar-left">
                <a href="{{ route('admin-portal.index') }}" class="topbar-brand">
                    <div class="brand-icon-box">🧰</div>
                    <div class="brand-text-block">
                        <span class="brand-title">TOOLROOM</span>
                        <span class="brand-sub">MANAGER</span>
                    </div>
                </a>
            </div>

            {{-- Center Search --}}
            <div class="topbar-search-box">
                <svg class="topbar-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="global-dashboard-search" class="topbar-search-input"
                       placeholder="Search tools, assets, staff..." onkeyup="filterGlobalDashboard(this.value)">
            </div>

            {{-- Right Actions --}}
            <div class="topbar-right">
                <button type="button" class="topbar-action-icon" title="Mesajlar">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </button>

                <button type="button" class="topbar-action-icon" title="Bildirimler">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(isset($stats['overdue_tools']) && $stats['overdue_tools'] > 0)
                        <span class="badge-dot">{{ $stats['overdue_tools'] }}</span>
                    @endif
                </button>

                <div class="user-profile-menu" title="{{ Auth::user()?->name ?? 'Admin' }}">
                    <div class="user-avatar-circle">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="user-info-text">
                        <span class="user-name-title">{{ Auth::user()?->name ?? 'Admin User' }}</span>
                        <span class="user-role-label">{{ Auth::user()?->role ?? 'Admin' }}</span>
                    </div>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--muted); margin-left:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <a href="/admin" class="topbar-action-icon" title="Ayarlar">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="admin-page-content">
            @yield('content')
        </main>
    </div>
</div>

{{-- Global Lightbox Modal --}}
<style>
.lb-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.92);
    backdrop-filter: blur(14px);
    z-index: 9999;
    display: none;
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

<div id="image-lightbox-modal" class="lb-overlay" onclick="closeImageLightbox(event)">
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

function filterGlobalDashboard(val) {
    var query = val.toLowerCase().trim();
    // Envanter tablosunu filtrele
    var inventoryRows = document.querySelectorAll('.inventory-row-item');
    inventoryRows.forEach(function(row) {
        var text = (row.getAttribute('data-search-text') || '').toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });

    // Personel zimmet kartlarını da filtrele
    var cards = document.querySelectorAll('.person-loan-card');
    cards.forEach(function(card) {
        var text = (card.getAttribute('data-search-text') || '').toLowerCase();
        card.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}
</script>

@stack('scripts')
</body>
</html>
