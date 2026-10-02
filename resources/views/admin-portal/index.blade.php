@extends('admin-portal.layout')
@section('title', 'Toolroom Manager — ' . ($currentToolroom?->name ?? 'Ağır Vasıta Takımhanesi'))

@push('styles')
<style>
    /* ─── Switcher Bar (Mockup Header Tabs) ─── */
    .toolroom-switch-container {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .toolroom-switch-tab {
        flex: 1;
        min-width: 220px;
        height: 52px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        text-decoration: none;
        transition: all .25s ease;
        border: 1px solid var(--border);
        background: #141a24;
        color: var(--muted);
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }

    .toolroom-switch-tab:hover {
        background: #1a2332;
        color: #ffffff;
        border-color: var(--border-light);
    }

    .toolroom-switch-tab.active {
        background: var(--accent);
        color: #0c1017;
        border-color: var(--accent);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }

    .toolroom-switch-tab svg {
        width: 22px;
        height: 22px;
    }

    /* ─── Page Title Bar ─── */
    .dashboard-title-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .dashboard-title-text {
        font-size: 20px;
        font-weight: 900;
        letter-spacing: 0.8px;
        color: #ffffff;
        text-transform: uppercase;
    }

    .dashboard-actions-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* ─── Top Charts Grid (Full Width 2-Cards Row) ─── */
    .charts-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    /* ─── Donut Chart Widgets ─── */
    .donut-card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding-top: 6px;
    }

    .donut-svg-wrapper {
        position: relative;
        width: 140px;
        height: 140px;
        flex-shrink: 0;
    }

    .donut-svg {
        transform: rotate(-90deg);
        width: 100%;
        height: 100%;
    }

    .donut-center-content {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .donut-center-label {
        font-size: 10px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .donut-center-value {
        font-size: 22px;
        font-weight: 900;
        color: #ffffff;
        line-height: 1;
        margin-top: 2px;
    }

    .donut-legend-list {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .donut-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 600;
        color: #cbd5e1;
    }

    .donut-legend-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .donut-legend-indicator {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        flex-shrink: 0;
    }

    .donut-legend-value {
        font-weight: 800;
        color: #ffffff;
        font-size: 13px;
    }

    /* ─── Inventory Table (Full Width) ─── */
    .table-container {
        overflow-x: auto;
        margin-top: 12px;
        border-radius: 8px;
    }

    .custom-admin-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
        text-align: left;
    }

    .custom-admin-table th {
        background: #131a24;
        color: #8b9bb4;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .custom-admin-table td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(38, 51, 70, 0.6);
        color: #e2e8f0;
        vertical-align: middle;
        background: #18202c;
    }

    .custom-admin-table tr:hover td {
        background: #1e2837;
    }

    .tool-thumb-img {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        object-fit: cover;
        background: #0d121a;
        border: 1px solid var(--border);
        cursor: pointer;
    }

    .tool-thumb-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: #131a24;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    /* ─── Collapsible Operations Accordion ─── */
    .operations-drawer {
        margin-top: 24px;
        border-radius: var(--radius-lg);
        background: var(--surface);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .operations-drawer-header {
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        background: #141b25;
        border-bottom: 1px solid var(--border);
    }

    /* Pagination Styling */
    .custom-pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
</style>
@endpush

@section('content')
{{-- 1. TAKIMHANE GEÇİŞ SEKMELERİ --}}
<div class="toolroom-switch-container">
    @forelse($allToolrooms as $room)
        @php
            $isRoomActive = ($roomId == $room->id);
            $isHeavy = (stripos($room->name, 'Ağır') !== false || stripos($room->code, 'AGIR') !== false || $room->id == 2);
        @endphp
        <a href="{{ route('admin-portal.index', ['toolroom_id' => $room->id, 'date' => $selectedDate]) }}"
           class="toolroom-switch-tab {{ $isRoomActive ? 'active' : '' }}">
            @if($isHeavy)
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
                <span>HEAVY VEHICLE (AĞIR VASITA)</span>
            @else
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.08 3.11H5.77L6.85 7zM19 17H5v-4.66l.12-.34h13.77l.11.34V17zM7.5 15c.83 0 1.5-.67 1.5-1.5S8.33 12 7.5 12 6 12.67 6 13.5 6.67 15 7.5 15zm9 0c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5z"/>
                </svg>
                <span>PASSENGER CAR (BİNEK & HTA)</span>
            @endif
        </a>
    @empty
        <a href="#" class="toolroom-switch-tab active">
            <span>HEAVY VEHICLE TOOLROOM</span>
        </a>
    @endforelse
</div>

{{-- 2. BAŞLIK & HIZLI AKSİYONLAR --}}
<div class="dashboard-title-bar">
    <div>
        <h1 class="dashboard-title-text">
            @if(stripos($currentToolroom?->name ?? '', 'Ağır') !== false || $roomId == 2)
                HEAVY VEHICLE TOOLROOM
            @elseif(stripos($currentToolroom?->name ?? '', 'Oto') !== false || $roomId == 1)
                PASSENGER CAR TOOLROOM
            @else
                {{ strtoupper($currentToolroom?->name ?? 'TOOLROOM MANAGER') }}
            @endif
        </h1>
        <div style="font-size:12px; color:var(--muted); margin-top:2px; display:flex; align-items:center; gap:8px;">
            <span style="color:#10b981; font-weight:700;">● Canlı Sistem Takibi</span>
            <span>|</span>
            <span>Tarih: {{ now()->format('d.m.Y H:i') }}</span>
            <span>|</span>
            <span>Toplam {{ $stats['total_tools'] }} Kayıtlı Takım</span>
        </div>
    </div>

    <div class="dashboard-actions-group">
        <button type="button" onclick="openAssignModal()" class="btn btn-accent" style="font-weight:800;">
            ➕ YENİ ZİMMET VER
        </button>
        <a href="{{ route('admin-portal.add-tool') }}" class="btn btn-ghost">
            🔧 Yeni Alet Ekle
        </a>
        <a href="{{ route('export.tools.excel', ['toolroom_id' => $roomId]) }}" class="btn btn-accent" style="background:#f59e0b; color:#0b0e14;">
            📊 EXCEL EXPORT
        </a>
    </div>
</div>

{{-- 3. ÜST GRAFİKLER SIRASI (2 KART YAN YANA - TAM GENİŞLİK) --}}
<div class="charts-row">
    {{-- CARD 1: ACTIVE LOANS STATUS --}}
    <div class="card">
        <div class="card-title">
            <span>ACTIVE LOANS STATUS</span>
            <span style="font-size:11px; color:var(--muted); font-weight:600;">Aktif Zimmetler</span>
        </div>

        <div class="donut-card-body">
            <div class="donut-svg-wrapper">
                <svg class="donut-svg" viewBox="0 0 36 36">
                    @php
                        $totalLoans = max(1, $activeLoansChart['total']);
                        $cats = $activeLoansChart['categories'];
                        $colors = ['#f59e0b', '#ea580c', '#eab308', '#6366f1', '#10b981'];
                        $offset = 0;
                        $index = 0;
                    @endphp

                    <circle cx="18" cy="18" r="14" fill="transparent" stroke="#222c3d" stroke-width="4"></circle>

                    @if($activeLoansChart['total'] == 0)
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#334155" stroke-width="4" stroke-dasharray="100 0"></circle>
                    @else
                        @foreach($cats as $catName => $catCount)
                            @php
                                $pct = ($catCount / $totalLoans) * 100;
                                $color = $colors[$index % count($colors)];
                                $dash = "{$pct} " . (100 - $pct);
                                $strokeOffset = -$offset;
                                $offset += $pct;
                                $index++;
                            @endphp
                            <circle cx="18" cy="18" r="14" fill="transparent"
                                    stroke="{{ $color }}" stroke-width="4.5"
                                    stroke-dasharray="{{ $dash }}"
                                    stroke-dashoffset="{{ $strokeOffset }}"></circle>
                        @endforeach
                    @endif
                </svg>
                <div class="donut-center-content">
                    <span class="donut-center-label">Total</span>
                    <span class="donut-center-value">{{ $activeLoansChart['total'] }}</span>
                </div>
            </div>

            <div class="donut-legend-list">
                @php $i = 0; @endphp
                @foreach($cats as $catName => $catCount)
                    @php $color = $colors[$i % count($colors)]; $i++; @endphp
                    <div class="donut-legend-item">
                        <div class="donut-legend-left">
                            <span class="donut-legend-indicator" style="background:{{ $color }};"></span>
                            <span style="white-space:nowrap; max-width:140px; overflow:hidden; text-overflow:ellipsis;" title="{{ $catName }}">
                                {{ $catName }}
                            </span>
                        </div>
                        <span class="donut-legend-value">{{ $catCount }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- CARD 2: TOOL AVAILABILITY --}}
    <div class="card">
        <div class="card-title">
            <span>TOOL AVAILABILITY</span>
            <span style="font-size:11px; color:var(--muted); font-weight:600;">Müsaitlik Oranı</span>
        </div>

        <div class="donut-card-body">
            @php
                $avPct = $availabilityStats['available_pct'];
                $usePct = $availabilityStats['in_use_pct'];
                $maintPct = $availabilityStats['maintenance_pct'];
            @endphp
            <div class="donut-svg-wrapper">
                <svg class="donut-svg" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="14" fill="transparent" stroke="#222c3d" stroke-width="4"></circle>
                    @if($availabilityStats['total'] == 0)
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#334155" stroke-width="4" stroke-dasharray="100 0"></circle>
                    @else
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#f59e0b" stroke-width="4.5"
                                stroke-dasharray="{{ $avPct }} {{ 100 - $avPct }}"
                                stroke-dashoffset="0"></circle>
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#ea580c" stroke-width="4.5"
                                stroke-dasharray="{{ $usePct }} {{ 100 - $usePct }}"
                                stroke-dashoffset="-{{ $avPct }}"></circle>
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#fbbf24" stroke-width="4.5"
                                stroke-dasharray="{{ $maintPct }} {{ 100 - $maintPct }}"
                                stroke-dashoffset="-{{ $avPct + $usePct }}"></circle>
                    @endif
                </svg>
                <div class="donut-center-content">
                    <span class="donut-center-label">Available</span>
                    <span class="donut-center-value" style="color:#f59e0b;">{{ $avPct }}%</span>
                </div>
            </div>

            <div class="donut-legend-list">
                <div class="donut-legend-item">
                    <div class="donut-legend-left">
                        <span class="donut-legend-indicator" style="background:#f59e0b;"></span>
                        <span>Available</span>
                    </div>
                    <span class="donut-legend-value">{{ $avPct }}% ({{ $availabilityStats['available'] }})</span>
                </div>
                <div class="donut-legend-item">
                    <div class="donut-legend-left">
                        <span class="donut-legend-indicator" style="background:#ea580c;"></span>
                        <span>In Use</span>
                    </div>
                    <span class="donut-legend-value">{{ $usePct }}% ({{ $availabilityStats['in_use'] }})</span>
                </div>
                <div class="donut-legend-item">
                    <div class="donut-legend-left">
                        <span class="donut-legend-indicator" style="background:#fbbf24;"></span>
                        <span>Maintenance</span>
                    </div>
                    <span class="donut-legend-value">{{ $maintPct }}% ({{ $availabilityStats['maintenance'] }})</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. ALT ENVANTER TABLOSU (TAM GENİŞLİK - EKRANA RAHATÇA SIĞAR) --}}
<div class="card" style="padding: 18px 20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
        <div>
            <h2 style="font-size:15px; font-weight:800; letter-spacing:0.8px; color:#fff; text-transform:uppercase;">
                TOOL INVENTORY & STATUS
            </h2>
            <p style="font-size:11px; color:var(--muted); margin-top:2px;">
                Canlı stok, zimmetli personel ve raf/göz konumları
            </p>
        </div>

        <form method="GET" action="{{ route('admin-portal.index') }}" style="display:flex; align-items:center; gap:8px;">
            <input type="hidden" name="toolroom_id" value="{{ $roomId }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="🔍 Takım adı, seri no veya barkod..."
                   class="form-input" style="padding:7px 12px; font-size:12px; width:240px; border-radius:8px;">
            <button type="submit" class="btn btn-ghost btn-sm" style="font-weight:700;">Ara</button>
            @if(request('search'))
                <a href="{{ route('admin-portal.index', ['toolroom_id' => $roomId, 'date' => $selectedDate]) }}" class="btn btn-ghost btn-sm" style="color:#f87171;">✕</a>
            @endif
        </form>
    </div>

    {{-- Canlı Tablo --}}
    <div class="table-container">
        <table class="custom-admin-table" id="inventory-table">
            <thead>
                <tr>
                    <th style="width:100px;">ID ↑</th>
                    <th>Tool Name</th>
                    <th>Category</th>
                    <th>Serial No.</th>
                    <th>Current Location / User</th>
                    <th>Return Date</th>
                    <th style="text-align:right;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryTools as $tool)
                    @php
                        $loan = $tool->activeLoan;
                        $isOverdue = $loan && ($loan->isOverdue() || $loan->status === 'overdue');
                        $userOrLocation = '-';
                        $returnDate = '-';

                        if ($tool->status === 'loaned' && $loan) {
                            $userOrLocation = $loan->personnel?->name ?? ($loan->loanedByUser?->name ?? 'Zimmette');
                            $returnDate = $loan->planned_return_at ? $loan->planned_return_at->format('d.m.Y H:i') : 'Bugün';
                        } elseif ($tool->slot) {
                            $bName = $tool->slot->shelf?->block?->name ?? '';
                            $sName = $tool->slot->shelf?->name ?? '';
                            $gName = $tool->slot->name ?? '';

                            $locParts = [];
                            if (!empty($bName)) $locParts[] = $bName;
                            if (!empty($sName)) $locParts[] = is_numeric($sName) ? "Raf {$sName}" : $sName;
                            if (!empty($gName)) $locParts[] = is_numeric($gName) ? "Göz {$gName}" : $gName;

                            $userOrLocation = !empty($locParts) ? implode(' / ', $locParts) : ($tool->slot->full_label ?? 'Depo / Raf');
                        } else {
                            $userOrLocation = 'Depo / Raf';
                        }

                        $toolCode = 'TKM-' . str_pad($tool->id, 4, '0', STR_PAD_LEFT);
                    @endphp
                    <tr class="inventory-row-item">
                        {{-- ID --}}
                        <td>
                            <span style="font-family:'JetBrains Mono',monospace; font-weight:700; color:#cbd5e1; font-size:12px;">
                                {{ $toolCode }}
                            </span>
                        </td>

                        {{-- Tool Name --}}
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                @if($tool->image)
                                    <img src="{{ Storage::url($tool->image) }}" alt="{{ $tool->name }}"
                                         class="tool-thumb-img"
                                         onclick="openImageLightbox('{{ Storage::url($tool->image) }}', '{{ addslashes($tool->name) }}')"
                                         title="Resmi büyütmek için tıklayın">
                                @else
                                    <div class="tool-thumb-placeholder">🔧</div>
                                @endif
                                <div>
                                    <div style="font-weight:700; color:#fff; font-size:13px;">{{ $tool->name }}</div>
                                    @if($tool->description)
                                        <div style="font-size:11px; color:var(--muted); max-width:260px; overflow:hidden; text-overflow:ellipsis;">
                                            {{ Str::limit($tool->description, 35) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td style="color:#cbd5e1; font-size:12px;">
                            {{ $tool->toolGroup?->name ?? ($tool->category ? \App\Models\Tool::CATEGORIES[$tool->category] ?? $tool->category : 'Genel') }}
                        </td>

                        {{-- Serial No --}}
                        <td>
                            <span style="font-family:'JetBrains Mono',monospace; font-size:12px; color:#94a3b8;">
                                {{ $tool->serial_no ?? ($tool->barcode ?? '—') }}
                            </span>
                        </td>

                        {{-- Current Location / User --}}
                        <td>
                            @if($tool->status === 'loaned')
                                <span style="color:#60a5fa; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                                    👤 {{ $userOrLocation }}
                                </span>
                            @else
                                <span style="color:#34d399; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
                                    📍 {{ $userOrLocation }}
                                </span>
                            @endif
                        </td>

                        {{-- Return Date --}}
                        <td>
                            @if($tool->status === 'loaned')
                                <span style="font-size:12px; font-weight:600; color:{{ $isOverdue ? '#f87171' : '#cbd5e1' }};">
                                    {{ $returnDate }}
                                </span>
                            @else
                                <span style="color:#64748b;">-</span>
                            @endif
                        </td>

                        {{-- Status Badges --}}
                        <td style="text-align:right;">
                            @if($tool->status === 'available')
                                <span class="status-badge status-available">Available</span>
                            @elseif($isOverdue)
                                <span class="status-badge status-overdue">Overdue</span>
                            @elseif($tool->status === 'loaned')
                                <span class="status-badge status-in-use">In Use</span>
                            @elseif($tool->status === 'maintenance')
                                <span class="status-badge status-maintenance">In Maintenance</span>
                            @else
                                <span class="status-badge status-maintenance">Scrapped</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:30px; color:var(--muted);">
                            Bu takımhanede kayıtlı parça bulunmuyor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Sayfalama Barı --}}
    <div class="custom-pagination-bar">
        <div style="font-size:12px; color:var(--muted);">
            Toplam <b>{{ $inventoryTools->total() }}</b> aletten <b>{{ $inventoryTools->firstItem() ?? 0 }}-{{ $inventoryTools->lastItem() ?? 0 }}</b> arası gösteriliyor.
        </div>
        <div>
            {{ $inventoryTools->links() }}
        </div>
    </div>
</div>

{{-- 5. GÜNLÜK HAREKETLER & PERSONEL ZİMMET GEÇMİŞİ (OPERASYONEL MODÜL) --}}
<div class="operations-drawer">
    <div class="operations-drawer-header" onclick="toggleOperationsDrawer()">
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:16px;">📋</span>
            <div>
                <span style="font-size:14px; font-weight:800; color:#fff; text-transform:uppercase;">
                    DETAYLI GÜNLÜK ZİMMET & İADE LOGLARI
                </span>
                <span style="font-size:11px; color:var(--muted); margin-left:8px;">
                    ({{ $selectedDate == today()->toDateString() ? 'Bugünkü Canlı Hareketler' : $carbonDate->format('d.m.Y') . ' Arşivi' }})
                </span>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:12px;">
            <input type="date" value="{{ $selectedDate }}" onchange="changeArchiveDate(this.value)" onclick="event.stopPropagation()"
                   style="background:var(--surface2); border:1px solid var(--border); color:#fff; padding:5px 10px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">

            <a href="{{ route('admin-portal.export-daily-pdf', ['date' => $selectedDate]) }}" target="_blank" onclick="event.stopPropagation()"
               class="btn btn-danger btn-xs" style="font-weight:700;">
                📄 Gün Sonu PDF
            </a>
            <svg id="drawer-arrow" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition:transform .2s;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    <div id="operations-drawer-body" style="padding:18px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:10px; margin-bottom:18px;">
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:10px 14px; border-left:3px solid #6366f1;">
                <div style="font-size:11px; font-weight:700; color:var(--muted);">GÜNÜN HAREKETİ</div>
                <div style="font-size:20px; font-weight:900; color:#fff; margin-top:2px;">{{ $dailyStats['total_movements'] }}</div>
            </div>
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:10px 14px; border-left:3px solid #f59e0b;">
                <div style="font-size:11px; font-weight:700; color:var(--muted);">HALEN DIŞARIDA</div>
                <div style="font-size:20px; font-weight:900; color:#fbbf24; margin-top:2px;">{{ $dailyStats['still_loaned'] }}</div>
            </div>
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:10px 14px; border-left:3px solid #10b981;">
                <div style="font-size:11px; font-weight:700; color:var(--muted);">İADE ALINAN</div>
                <div style="font-size:20px; font-weight:900; color:#34d399; margin-top:2px;">{{ $dailyStats['returned_today'] }}</div>
            </div>
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:10px 14px; border-left:3px solid {{ $dailyStats['overdue_count'] > 0 ? '#ef4444' : '#64748b' }};">
                <div style="font-size:11px; font-weight:700; color:var(--muted);">GECİKMİŞ</div>
                <div style="font-size:20px; font-weight:900; color:{{ $dailyStats['overdue_count'] > 0 ? '#f87171' : '#94a3b8' }}; margin-top:2px;">{{ $dailyStats['overdue_count'] }}</div>
            </div>
        </div>

        <div id="grouped-loans-container">
            @forelse($groupedLoans as $group)
                <div class="person-loan-card" style="background:#141b25; border:1px solid {{ $group['has_overdue'] ? 'rgba(239,68,68,0.5)' : 'var(--border)' }}; border-radius:12px; padding:14px; margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="width:32px; height:32px; border-radius:8px; background:rgba(245,158,11,0.2); color:#f59e0b; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px;">
                                👤
                            </div>
                            <div>
                                <span style="font-weight:800; color:#fff; font-size:14px;">{{ $group['personnel_name'] }}</span>
                                @if($group['badge_number'] !== '—')
                                    <span style="font-family:'JetBrains Mono',monospace; font-size:11px; background:rgba(255,255,255,0.08); padding:2px 6px; border-radius:4px; color:#e2e8f0; margin-left:6px;">
                                        Sicil: {{ $group['badge_number'] }}
                                    </span>
                                @endif
                                <span style="font-size:11px; color:var(--muted); margin-left:6px;">{{ $group['personnel_dept'] }}</span>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; gap:6px;">
                            @if($group['personnel_id'])
                                <button type="button" onclick="openAssignModal({{ $group['personnel_id'] }}, '{{ addslashes($group['personnel_name']) }}')"
                                        class="btn btn-accent btn-xs" style="font-weight:700;">
                                    ➕ Parça Ver
                                </button>
                            @endif
                        </div>
                    </div>

                    <div style="overflow-x:auto;">
                        <table style="width:100%; font-size:12px; border-collapse:collapse;">
                            <tbody>
                                @foreach($group['loans'] as $loan)
                                    <tr id="loan-row-{{ $loan->id }}" style="border-top:1px solid rgba(255,255,255,0.06);">
                                        <td style="padding:8px 6px; color:#fff; font-weight:700;">
                                            {{ $loan->tool?->name ?? 'Silinmiş Takım' }}
                                            <span style="color:#f59e0b; font-family:'JetBrains Mono',monospace; font-size:11px; margin-left:6px;">
                                                SN: {{ $loan->tool?->serial_no ?? 'Yok' }}
                                            </span>
                                        </td>
                                        <td style="padding:8px 6px; color:var(--muted);">
                                            ⏱️ {{ $loan->loaned_at?->format('d.m H:i') ?? '-' }}
                                        </td>
                                        <td style="padding:8px 6px;">
                                            @if($loan->status === 'returned')
                                                <span class="status-badge status-available" style="font-size:10px;">İade Edildi</span>
                                            @elseif($loan->isOverdue() || $loan->status === 'overdue')
                                                <span class="status-badge status-overdue" style="font-size:10px;">🚨 Gecikmiş</span>
                                            @else
                                                <span class="status-badge status-in-use" style="font-size:10px;">Zimmette</span>
                                            @endif
                                        </td>
                                        <td style="padding:8px 6px; text-align:right;">
                                            @if($loan->status !== 'returned')
                                                <button type="button" class="btn btn-success btn-xs"
                                                        onclick="returnSingleLoan({{ $loan->id }}, '{{ addslashes($loan->tool?->name ?? 'Takım') }}', '{{ addslashes($group['personnel_name']) }}')">
                                                    ↩️ İade Al
                                                </button>
                                            @else
                                                <span style="color:#34d399; font-size:11px; font-weight:700;">✓ Teslim Alındı</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:24px; color:var(--muted); font-size:13px;">
                    Bugüne veya seçilen tarihe ait zimmet kaydı bulunamadı.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- 6. HIZLI PARÇA ZİMMETLEME MODALI --}}
<div id="assign-modal" class="hidden" style="position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px); z-index:9999; display:flex; align-items:center; justify-content:center; padding:16px;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:18px; width:100%; max-width:480px; padding:24px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid var(--border); padding-bottom:14px;">
            <div style="font-weight:900; font-size:16px; color:#fff; display:flex; align-items:center; gap:8px;">
                <span style="color:#f59e0b;">➕</span>
                <span>Zaman Damgalı Parça Zimmetle</span>
            </div>
            <button onclick="closeAssignModal()" style="background:transparent; border:none; color:var(--muted); font-size:20px; cursor:pointer; padding:4px;">✕</button>
        </div>

        <form id="assign-tool-form" onsubmit="submitAssignForm(event)">
            <div class="form-group">
                <label class="form-label" for="assign-personnel-id">👤 Personel Seçin</label>
                <select class="form-input" id="assign-personnel-id" name="personnel_id" required>
                    <option value="">-- Personel Seçin --</option>
                    @foreach($personnelList as $person)
                        <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->badge_number }} - {{ $person->department ?? 'Genel' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="assign-tool-id">🔧 Parça / Takım Seçin (Müsait Olanlar)</label>
                <select class="form-input" id="assign-tool-id" name="tool_id" required>
                    <option value="">-- Parça Seçin ({{ count($availableTools) }} adet hazır) --</option>
                    @foreach($availableTools as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->name }} [SN: {{ $t->serial_no ?? 'Yok' }}]
                            @if($t->slot) - ({{ $t->slot->shelf?->block?->name ?? '' }} / {{ $t->slot->shelf?->name ?? '' }} / {{ $t->slot->name ?? '' }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="assign-days">⏳ İade Süresi (Gün)</label>
                <input class="form-input" type="number" id="assign-days" name="days" value="7" min="1" max="90" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="assign-notes">📝 Zimmet Notu (İsteğe bağlı)</label>
                <textarea class="form-input" id="assign-notes" name="notes" rows="2" placeholder="İş emri veya araç plakası..." style="resize:none;"></textarea>
            </div>

            <div style="background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); border-radius:8px; padding:10px 12px; margin-bottom:16px; font-size:12px; color:#fcd34d;">
                ⏱️ <b>Zaman Damgası:</b> Parça kaydedildiği anda tam saniyesiyle sisteme mühürlenir.
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeAssignModal()" class="btn btn-ghost">İptal</button>
                <button type="submit" id="assign-submit-btn" class="btn btn-accent" style="font-weight:800;">💾 Zimmeti Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function changeArchiveDate(dateStr) {
    if (!dateStr) return;
    var url = new URL(window.location.href);
    url.searchParams.set('date', dateStr);
    window.location.href = url.toString();
}

function toggleOperationsDrawer() {
    var body = document.getElementById('operations-drawer-body');
    var arrow = document.getElementById('drawer-arrow');
    if (!body) return;
    if (body.style.display === 'none') {
        body.style.display = 'block';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    } else {
        body.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(-90deg)';
    }
}

function openAssignModal(personnelId, personnelName) {
    var modal = document.getElementById('assign-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    if (personnelId) {
        var sel = document.getElementById('assign-personnel-id');
        if (sel) sel.value = personnelId;
    }
}

function closeAssignModal() {
    var modal = document.getElementById('assign-modal');
    if (modal) modal.classList.add('hidden');
}

function submitAssignForm(e) {
    e.preventDefault();
    var btn = document.getElementById('assign-submit-btn');
    if (btn) { btn.disabled = true; btn.innerText = 'Kaydediliyor...'; }

    var form = document.getElementById('assign-tool-form');
    var formData = new FormData(form);

    fetch("{{ route('admin-portal.assign-loan') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Parça başarıyla zimmetlendi!');
            window.location.reload();
        } else {
            alert('Hata: ' + (data.message || 'Zimmet verilemedi'));
            if (btn) { btn.disabled = false; btn.innerText = '💾 Zimmeti Kaydet'; }
        }
    })
    .catch(err => {
        alert('İşlem başarısız oldu.');
        if (btn) { btn.disabled = false; btn.innerText = '💾 Zimmeti Kaydet'; }
    });
}

function returnSingleLoan(loanId, toolName, personName) {
    if (!confirm(toolName + ' parçasını (' + personName + ') iade almak istiyor musunuz?')) return;

    fetch("{{ url('admin-portal/loans') }}/" + loanId + "/return", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Parça başarıyla iade alındı!');
            window.location.reload();
        } else {
            alert('Hata: ' + (data.message || 'İade alınamadı'));
        }
    })
    .catch(err => alert('İşlem başarısız oldu.'));
}
</script>
@endpush
