@extends('portal.layout')
@section('title', 'Ana Sayfa')

@section('content')
{{-- Kullanıcı Karşılama --}}
<div style="padding: 20px 16px 0;">
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#f97316,#ea6c0d);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div style="font-size:18px;font-weight:700;">Merhaba, {{ explode(' ', $user->name)[0] }}! 👋</div>
            <div class="text-muted">{{ now()->format('d.m.Y') }}</div>
        </div>
    </div>
</div>

{{-- Personel kaydı yoksa uyarı --}}
@if(! $personnel)
    <div class="alert alert-warn mt-2">
        ⚠️ Hesabınıza henüz personel kaydı bağlanmamış. Zimmet alabilmek için yöneticinizle iletişime geçin.
    </div>
@endif

{{-- Gecikmiş zimmet uyarısı --}}
@if($overdueCount > 0)
    <div class="alert alert-danger mt-2" style="margin-top:12px;">
        🚨 <strong>{{ $overdueCount }}</strong> gecikmiş zimmetiniz var! Lütfen en kısa sürede iade edin.
    </div>
@endif

{{-- Hızlı Aksiyon Butonları --}}
<div style="padding: 16px; display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:4px;">
    <a href="{{ route('portal.scan') }}" class="btn btn-primary" style="flex-direction:column;padding:18px 12px;gap:6px;border-radius:16px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <span style="font-size:13px;">QR ile<br>Zimmet Al</span>
    </a>
    <a href="{{ route('portal.return') }}" class="btn btn-ghost" style="flex-direction:column;padding:18px 12px;gap:6px;border-radius:16px;border-color:var(--border);">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.13"/>
        </svg>
        <span style="font-size:13px;">Alet<br>İade Et</span>
    </a>
</div>

{{-- Aktif Zimmetler --}}
<div class="card">
    <div class="card-title">
        <span>Aktif Zimmetlerim</span>
        <span style="float:right;background:rgba(249,115,22,.15);color:#f97316;padding:2px 8px;border-radius:99px;font-size:11px;">
            {{ $activeLoans->count() }}
        </span>
    </div>

    @forelse($activeLoans as $loan)
        <div class="loan-item">
            <div class="loan-icon">🔧</div>
            <div class="loan-info">
                <div class="loan-name">{{ $loan->tool?->name ?? 'Silinmiş Alet' }}</div>
                <div class="loan-meta">
                    {{ $loan->tool?->serial_no ?? '-' }} •
                    {{ $loan->loaned_at?->format('d.m.Y') ?? '-' }}
                </div>
                <div class="loan-meta mt-1">
                    @if($loan->isOverdue())
                        <span class="badge badge-overdue">🚨 {{ $loan->overdue_days }} gün gecikmiş</span>
                    @else
                        <span class="badge badge-active">İade: {{ $loan->planned_return_at->format('d.m.Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center" style="padding: 24px 0;">
            <div style="font-size:40px;margin-bottom:8px;">✅</div>
            <div style="font-size:14px;color:var(--muted);">Aktif zimmetiniz bulunmuyor.</div>
        </div>
    @endforelse

    @if($activeLoans->count() > 0)
        <a href="{{ route('portal.my-loans') }}" class="btn btn-ghost mt-2" style="font-size:13px;padding:10px;">
            Tüm Geçmişi Gör →
        </a>
    @endif
</div>

{{-- İstatistikler --}}
@if($personnel)
<div style="padding: 0 16px 16px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:14px;">
        <div style="font-size:28px;font-weight:800;color:#f97316;">{{ $activeLoans->count() }}</div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px;">Aktif Zimmet</div>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:14px;">
        <div style="font-size:28px;font-weight:800;color:{{ $overdueCount > 0 ? '#ef4444' : '#22c55e' }};">{{ $overdueCount }}</div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px;">Gecikmiş</div>
    </div>
</div>
@endif
@endsection
