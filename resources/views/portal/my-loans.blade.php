@extends('portal.layout')
@section('title', 'Zimmetlerim')

@section('content')
<div style="padding:16px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">📋 Zimmetlerim</h2>
    <p class="text-muted" style="margin-bottom:16px;">Tüm aktif ve geçmiş zimmetleriniz</p>

    @forelse($loans as $loan)
        <div class="card" style="margin:0 0 10px;padding:14px;
            {{ $loan->isOverdue() ? 'border-color:rgba(239,68,68,.4);' : '' }}">
            <div style="display:flex;align-items:flex-start;gap:10px;">
                @if($loan->tool?->image)
                    <img src="{{ Storage::url($loan->tool->image) }}" alt="{{ $loan->tool->name }}"
                         onclick="openImageLightbox('{{ Storage::url($loan->tool->image) }}', '{{ addslashes($loan->tool->name) }}')"
                         style="width:44px;height:44px;border-radius:10px;object-fit:cover;cursor:pointer;flex-shrink:0;border:1px solid var(--border);"
                         title="Resmi büyütmek için tıklayın">
                @else
                    <div style="width:44px;height:44px;border-radius:10px;
                        background: {{ $loan->status === 'returned' ? 'rgba(34,197,94,.1)' : ($loan->isOverdue() ? 'rgba(239,68,68,.1)' : 'rgba(249,115,22,.1)') }};
                        display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                        {{ $loan->status === 'returned' ? '✅' : ($loan->isOverdue() ? '🚨' : '🔧') }}
                    </div>
                @endif
                <div style="flex:1;min-width:0;">
                    <div style="font-size:15px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $loan->tool?->name ?? 'Silinmiş Alet' }}
                    </div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px;">
                        {{ $loan->tool?->serial_no ?? '-' }}
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
                        <span class="badge {{ $loan->status === 'returned' ? 'badge-active' : ($loan->isOverdue() ? 'badge-overdue' : 'badge-active') }}">
                            {{ $loan->status_label }}
                        </span>
                        <span style="font-size:11px;color:var(--muted);align-self:center;">
                            {{ $loan->loaned_at->format('d.m.Y') }} alındı
                        </span>
                    </div>

                    @if($loan->status === 'returned' && $loan->returned_at)
                        <div style="font-size:11px;color:var(--muted);margin-top:4px;">
                            ↩️ {{ $loan->returned_at->format('d.m.Y H:i') }} iade edildi
                        </div>
                        @if($loan->return_photo)
                            <a href="javascript:void(0)" onclick="openImageLightbox('{{ Storage::url($loan->return_photo) }}', 'İade Fotoğrafı ({{ addslashes($loan->tool?->name ?? 'Alet') }})')"
                               style="display:inline-block;margin-top:6px;font-size:11px;color:#f97316;text-decoration:none;font-weight:600;">
                                📸 İade Fotoğrafı Önizle →
                            </a>
                        @endif
                    @elseif($loan->planned_return_at)
                        <div style="font-size:11px;color:{{ $loan->isOverdue() ? '#f87171' : 'var(--muted)' }};margin-top:4px;">
                            📅 İade: {{ $loan->planned_return_at->format('d.m.Y') }}
                            @if($loan->isOverdue())
                                <strong>({{ $loan->overdue_days }} gün gecikmiş!)</strong>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card" style="text-align:center;padding:40px 16px;">
            <div style="font-size:48px;margin-bottom:12px;">📭</div>
            <div style="font-size:15px;font-weight:600;">Henüz zimmetiniz yok</div>
            <div class="text-muted" style="margin-top:6px;">QR ile zimmet alın</div>
            <a href="{{ route('portal.scan') }}" class="btn btn-primary" style="margin-top:16px;max-width:200px;margin-left:auto;margin-right:auto;">
                📷 QR Tara
            </a>
        </div>
    @endforelse

    {{ $loans->links() }}
</div>
@endsection
