@extends('portal.layout')
@section('title', 'Profilim')

@section('content')
<div style="padding:16px;">
    {{-- Kullanıcı Kartı --}}
    <div style="text-align:center;padding:28px 16px 20px;">
        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#f97316,#ea6c0d);display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:800;color:#fff;margin:0 auto 12px;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div style="font-size:20px;font-weight:700;">{{ $user->name }}</div>
        <div style="font-size:13px;color:var(--muted);margin-top:4px;">{{ $user->email }}</div>
        <div style="margin-top:8px;">
            @php
                $roleLabels = ['super_admin' => '👑 Süper Admin', 'takimhane_sor' => '🔑 Takımhane Sorumlusu', 'personel' => '👤 Personel'];
            @endphp
            <span class="badge badge-active" style="font-size:12px;padding:4px 12px;">
                {{ $roleLabels[$user->role] ?? $user->role }}
            </span>
        </div>
    </div>

    {{-- Şifre Değiştir --}}
    <div class="card">
        <div class="card-title">🔒 Şifre Değiştir</div>

        <form action="{{ route('portal.update-password') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Mevcut Şifre</label>
                <input class="form-input" type="password" name="current_password" placeholder="••••••••" required autocomplete="current-password">
                @error('current_password')<div style="font-size:12px;color:#f87171;margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Yeni Şifre</label>
                <input class="form-input" type="password" name="password" placeholder="En az 8 karakter" required autocomplete="new-password">
                @error('password')<div style="font-size:12px;color:#f87171;margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Yeni Şifre (Tekrar)</label>
                <input class="form-input" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">🔒 Şifremi Güncelle</button>
        </form>
    </div>

    {{-- Çıkış --}}
    <div class="card" style="margin-top:0;">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-ghost" style="color:#f87171;border-color:rgba(239,68,68,.3);">
                🚪 Çıkış Yap
            </button>
        </form>
    </div>

    <div class="text-center text-muted" style="margin-top:16px;font-size:11px;">
        Takımhane YS v1.0 — {{ date('Y') }}
    </div>
</div>
@endsection
