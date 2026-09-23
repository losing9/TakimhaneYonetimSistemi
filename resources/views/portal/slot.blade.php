@extends('portal.layout')
@section('title', $slot->full_label . ' — Göz')

@push('styles')
<style>
.slot-hero {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-bottom: 1px solid var(--border);
    padding: 20px 16px 16px;
}
.slot-breadcrumb {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.slot-title { font-size: 22px; font-weight: 800; color: var(--text); }
.slot-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 14px;
}
.stat-box {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 10px;
    text-align: center;
}
.stat-num { font-size: 22px; font-weight: 800; }
.stat-lbl { font-size: 11px; color: var(--muted); margin-top: 2px; }

.tool-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px;
    margin: 0 16px 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: border-color .2s, transform .15s;
}
.tool-card:active { transform: scale(.98); }
.tool-card.available   { border-left: 3px solid var(--success); }
.tool-card.loaned      { border-left: 3px solid var(--warn); }
.tool-card.maintenance { border-left: 3px solid #38bdf8; }
.tool-card.scrapped    { border-left: 3px solid var(--muted); }

.tool-img {
    width: 52px; height: 52px;
    border-radius: 12px;
    object-fit: cover;
    flex-shrink: 0;
    cursor: pointer;
    transition: opacity .2s;
}
.tool-img:active { opacity: .7; }
.tool-img-placeholder {
    width: 52px; height: 52px;
    border-radius: 12px;
    background: var(--surface2);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0;
}
.tool-body { flex: 1; min-width: 0; }
.tool-name   { font-size: 14px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tool-serial { font-size: 12px; color: var(--muted); font-family: monospace; margin-top: 2px; }
.tool-loan-info { font-size: 12px; color: #fbbf24; margin-top: 4px; }

.status-pill {
    display: inline-flex; align-items: center;
    padding: 4px 10px; border-radius: 99px;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.pill-available   { background: rgba(34,197,94,.15);  color: #4ade80; }
.pill-loaned      { background: rgba(245,158,11,.15); color: #fbbf24; }
.pill-maintenance { background: rgba(56,189,248,.15); color: #38bdf8; }
.pill-scrapped    { background: rgba(148,163,184,.12);color: #94a3b8; }

.section-label {
    font-size: 11px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .06em;
    padding: 14px 16px 6px;
}
.empty-slot { text-align: center; padding: 48px 24px; color: var(--muted); }
.empty-slot .icon { font-size: 56px; margin-bottom: 12px; }
</style>
@endpush

@section('content')

{{-- Slot Hero --}}
<div class="slot-hero">
    <div class="slot-breadcrumb">
        <a href="{{ route('portal.index') }}" style="color:var(--muted);text-decoration:none;">Ana Sayfa</a>
        <span>›</span>
        <span>{{ $slot->shelf->block->name }}</span>
        <span>›</span>
        <span>{{ $slot->shelf->name }}</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">📦</div>
        <div>
            <div class="slot-title">{{ $slot->name }}</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">
                {{ $slot->shelf->block->name }} / {{ $slot->shelf->name }}
                &nbsp;·&nbsp; Kapasite: {{ $tools->count() }} / {{ $slot->capacity }}
            </div>
        </div>
    </div>
    <div class="slot-stats">
        <div class="stat-box">
            <div class="stat-num" style="color:#4ade80;">{{ $available }}</div>
            <div class="stat-lbl">Müsait</div>
        </div>
        <div class="stat-box">
            <div class="stat-num" style="color:#fbbf24;">{{ $loaned }}</div>
            <div class="stat-lbl">Zimmette</div>
        </div>
        <div class="stat-box">
            <div class="stat-num" style="color:#94a3b8;">{{ $maintenance }}</div>
            <div class="stat-lbl">Bakımda</div>
        </div>
    </div>
</div>

{{-- Alet Listesi --}}
@if($tools->isEmpty())
    <div class="empty-slot">
        <div class="icon">📭</div>
        <p>Bu gözde henüz kayıtlı alet yok.</p>
    </div>
@else
    <div class="section-label">Bu Gözdeki Aletler ({{ $tools->count() }})</div>

    @foreach($tools as $tool)
        @php
            $sc = match($tool->status) { 'available'=>'available','loaned'=>'loaned','maintenance'=>'maintenance',default=>'scrapped' };
            $sl = match($tool->status) { 'available'=>'✅ Müsait','loaned'=>'📤 Zimmette','maintenance'=>'🔧 Bakımda',default=>'🗑️ Hurda' };
        @endphp
        <div class="tool-card {{ $sc }}">
            @if($tool->image)
                <img class="tool-img"
                     src="{{ Storage::url($tool->image) }}"
                     alt="{{ $tool->name }}"
                     loading="lazy"
                     onclick="openImageLightbox('{{ Storage::url($tool->image) }}', '{{ addslashes($tool->name) }}')"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="tool-img-placeholder" style="display:none;">🔧</div>
            @else
                <div class="tool-img-placeholder">🔧</div>
            @endif

            <div class="tool-body">
                <div class="tool-name">{{ $tool->name }}</div>
                <div class="tool-serial"># {{ $tool->serial_no ?? '—' }}</div>
                @if($tool->category_label)
                    <div class="tool-serial">{{ $tool->category_label }}</div>
                @endif
                @if($tool->status === 'loaned' && $tool->activeLoan)
                    <div class="tool-loan-info">
                        👤 {{ $tool->activeLoan->personnel?->name ?? '—' }}
                        · {{ $tool->activeLoan->loaned_at?->format('d.m.Y') }}
                    </div>
                @endif
            </div>

            <span class="status-pill pill-{{ $sc }}">{{ $sl }}</span>
        </div>
    @endforeach
@endif

<div style="height:24px;"></div>
@endsection
