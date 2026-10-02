<x-filament-panels::page>
<style>
    /* ─── Switcher Bar ─── */
    .f-toolroom-switch-container {
        display: flex;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .f-toolroom-switch-tab {
        flex: 1;
        min-width: 220px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        cursor: pointer;
        transition: all .25s ease;
        border: 1px solid #263346;
        background: #141a24;
        color: #8b9bb4;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }

    .f-toolroom-switch-tab:hover {
        background: #1a2332;
        color: #ffffff;
        border-color: #32435b;
    }

    .f-toolroom-switch-tab.active {
        background: #f59e0b;
        color: #0c1017;
        border-color: #f59e0b;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }

    .f-toolroom-switch-tab svg {
        width: 22px;
        height: 22px;
    }

    /* ─── Page Title Bar ─── */
    .f-dashboard-title-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .f-dashboard-title-text {
        font-size: 20px;
        font-weight: 900;
        letter-spacing: 0.8px;
        color: #ffffff;
        text-transform: uppercase;
    }

    /* ─── Top Charts Grid (Full Width 2-Cards Row) ─── */
    .f-charts-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    /* ─── Card Base ─── */
    .f-card {
        background: #18202c;
        border: 1px solid #263346;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.25);
    }

    .f-card-title {
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.8px;
        color: #cbd5e1;
        text-transform: uppercase;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .f-donut-card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding-top: 6px;
    }

    .f-donut-svg-wrapper {
        position: relative;
        width: 140px;
        height: 140px;
        flex-shrink: 0;
    }

    .f-donut-svg {
        transform: rotate(-90deg);
        width: 100%;
        height: 100%;
    }

    .f-donut-center-content {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .f-donut-center-label {
        font-size: 10px;
        font-weight: 700;
        color: #8b9bb4;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .f-donut-center-value {
        font-size: 22px;
        font-weight: 900;
        color: #ffffff;
        line-height: 1;
        margin-top: 2px;
    }

    .f-donut-legend-list {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .f-donut-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 600;
        color: #cbd5e1;
    }

    .f-donut-legend-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .f-donut-legend-indicator {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        flex-shrink: 0;
    }

    .f-donut-legend-value {
        font-weight: 800;
        color: #ffffff;
        font-size: 13px;
    }

    /* ─── Inventory Table (Full Width) ─── */
    .f-table-container {
        overflow-x: auto;
        margin-top: 12px;
        border-radius: 8px;
    }

    .f-custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
        text-align: left;
    }

    .f-custom-table th {
        background: #131a24;
        color: #8b9bb4;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        padding: 12px 14px;
        border-bottom: 1px solid #263346;
        white-space: nowrap;
    }

    .f-custom-table td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(38, 51, 70, 0.6);
        color: #e2e8f0;
        vertical-align: middle;
        background: #18202c;
    }

    .f-custom-table tr:hover td {
        background: #1e2837;
    }

    /* Badges */
    .f-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    .f-status-available { background: #064e3b; color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); }
    .f-status-in-use    { background: #1e3a8a; color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); }
    .f-status-overdue   { background: #7f1d1d; color: #f87171; border: 1px solid rgba(248, 113, 113, 0.35); }
    .f-status-maintenance { background: #78350f; color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }

    .f-btn-accent {
        background: #f59e0b;
        color: #0c1017;
        font-weight: 800;
        font-size: 12px;
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        transition: all .2s;
    }
    .f-btn-accent:hover {
        background: #fbbf24;
        color: #000;
        transform: translateY(-1px);
    }
</style>

{{-- 1. TAKIMHANE GEÇİŞ SEKMELERİ --}}
<div class="f-toolroom-switch-container">
    @foreach($this->toolrooms as $room)
        @php
            $isRoomActive = ($this->selectedToolroomId == $room->id);
            $isHeavy = (stripos($room->name, 'Ağır') !== false || stripos($room->code, 'AGIR') !== false || $room->id == 2);
        @endphp
        <button type="button" wire:click="switchToolroom({{ $room->id }})"
                class="f-toolroom-switch-tab {{ $isRoomActive ? 'active' : '' }}">
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
        </button>
    @endforeach
</div>

{{-- 2. BAŞLIK BARI --}}
<div class="f-dashboard-title-bar">
    <div>
        <h1 class="f-dashboard-title-text">
            @if(stripos($this->currentToolroom?->name ?? '', 'Ağır') !== false || $this->selectedToolroomId == 2)
                HEAVY VEHICLE TOOLROOM
            @elseif(stripos($this->currentToolroom?->name ?? '', 'Oto') !== false || $this->selectedToolroomId == 1)
                PASSENGER CAR TOOLROOM
            @else
                {{ strtoupper($this->currentToolroom?->name ?? 'TOOLROOM MANAGER') }}
            @endif
        </h1>
        <div style="font-size:12px; color:#8b9bb4; margin-top:2px;">
            <span>Toplam {{ $this->availabilityStats['total'] }} Kayıtlı Takım</span>
        </div>
    </div>

    <div style="display:flex; align-items:center; gap:10px;">
        <a href="{{ route('admin-portal.index') }}" class="f-btn-accent" style="background:#1f2837; color:#fff; border:1px solid #32435b;">
            ↗️ Yönetici Portalı Tam Ekran
        </a>
        <a href="{{ route('export.tools.excel', ['toolroom_id' => $this->selectedToolroomId]) }}" class="f-btn-accent">
            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1.8 14.8l-1.4 1.4-2.4-2.4-2.4 2.4-1.4-1.4 2.4-2.4-2.4-2.4 1.4-1.4 2.4 2.4 2.4-2.4 1.4 1.4-2.4 2.4 2.4zM13 9V3.5L18.5 9H13z"/>
            </svg>
            EXCEL EXPORT
        </a>
    </div>
</div>

{{-- 3. ÜST GRAFİKLER SIRASI (2 KART YAN YANA, TAM GENİŞLİK) --}}
<div class="f-charts-row">
    {{-- CARD 1: ACTIVE LOANS STATUS --}}
    @php
        $chart1 = $this->activeLoansChart;
        $tot1 = max(1, $chart1['total']);
        $cats1 = $chart1['categories'];
        $colors1 = ['#f59e0b', '#ea580c', '#eab308', '#6366f1', '#10b981'];
        $off1 = 0;
        $idx1 = 0;
    @endphp
    <div class="f-card">
        <div class="f-card-title">
            <span>ACTIVE LOANS STATUS</span>
            <span style="font-size:11px; color:#8b9bb4; font-weight:600;">Aktif Zimmetler</span>
        </div>

        <div class="f-donut-card-body">
            <div class="f-donut-svg-wrapper">
                <svg class="f-donut-svg" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="14" fill="transparent" stroke="#222c3d" stroke-width="4"></circle>
                    @if($chart1['total'] == 0)
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#334155" stroke-width="4" stroke-dasharray="100 0"></circle>
                    @else
                        @foreach($cats1 as $catName => $catCount)
                            @php
                                $pct = ($catCount / $tot1) * 100;
                                $color = $colors1[$idx1 % count($colors1)];
                                $dash = "{$pct} " . (100 - $pct);
                                $strokeOffset = -$off1;
                                $off1 += $pct;
                                $idx1++;
                            @endphp
                            <circle cx="18" cy="18" r="14" fill="transparent"
                                    stroke="{{ $color }}" stroke-width="4.5"
                                    stroke-dasharray="{{ $dash }}"
                                    stroke-dashoffset="{{ $strokeOffset }}"></circle>
                        @endforeach
                    @endif
                </svg>
                <div class="f-donut-center-content">
                    <span class="f-donut-center-label">Total</span>
                    <span class="f-donut-center-value">{{ $chart1['total'] }}</span>
                </div>
            </div>

            <div class="f-donut-legend-list">
                @php $k = 0; @endphp
                @foreach($cats1 as $catName => $catCount)
                    @php $color = $colors1[$k % count($colors1)]; $k++; @endphp
                    <div class="f-donut-legend-item">
                        <div class="f-donut-legend-left">
                            <span class="f-donut-legend-indicator" style="background:{{ $color }};"></span>
                            <span style="max-width:140px; overflow:hidden; text-overflow:ellipsis;" title="{{ $catName }}">
                                {{ $catName }}
                            </span>
                        </div>
                        <span class="f-donut-legend-value">{{ $catCount }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- CARD 2: TOOL AVAILABILITY --}}
    @php
        $avail = $this->availabilityStats;
        $avP = $avail['available_pct'];
        $useP = $avail['in_use_pct'];
        $mnP = $avail['maintenance_pct'];
    @endphp
    <div class="f-card">
        <div class="f-card-title">
            <span>TOOL AVAILABILITY</span>
            <span style="font-size:11px; color:#8b9bb4; font-weight:600;">Müsaitlik Oranı</span>
        </div>

        <div class="f-donut-card-body">
            <div class="f-donut-svg-wrapper">
                <svg class="f-donut-svg" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="14" fill="transparent" stroke="#222c3d" stroke-width="4"></circle>
                    @if($avail['total'] == 0)
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#334155" stroke-width="4" stroke-dasharray="100 0"></circle>
                    @else
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#f59e0b" stroke-width="4.5"
                                stroke-dasharray="{{ $avP }} {{ 100 - $avP }}" stroke-dashoffset="0"></circle>
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#ea580c" stroke-width="4.5"
                                stroke-dasharray="{{ $useP }} {{ 100 - $useP }}" stroke-dashoffset="-{{ $avP }}"></circle>
                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#fbbf24" stroke-width="4.5"
                                stroke-dasharray="{{ $mnP }} {{ 100 - $mnP }}" stroke-dashoffset="-{{ $avP + $useP }}"></circle>
                    @endif
                </svg>
                <div class="f-donut-center-content">
                    <span class="f-donut-center-label">Available</span>
                    <span class="f-donut-center-value" style="color:#f59e0b;">{{ $avP }}%</span>
                </div>
            </div>

            <div class="f-donut-legend-list">
                <div class="f-donut-legend-item">
                    <div class="f-donut-legend-left">
                        <span class="f-donut-legend-indicator" style="background:#f59e0b;"></span>
                        <span>Available</span>
                    </div>
                    <span class="f-donut-legend-value">{{ $avP }}% ({{ $avail['available'] }})</span>
                </div>
                <div class="f-donut-legend-item">
                    <div class="f-donut-legend-left">
                        <span class="f-donut-legend-indicator" style="background:#ea580c;"></span>
                        <span>In Use</span>
                    </div>
                    <span class="f-donut-legend-value">{{ $useP }}% ({{ $avail['in_use'] }})</span>
                </div>
                <div class="f-donut-legend-item">
                    <div class="f-donut-legend-left">
                        <span class="f-donut-legend-indicator" style="background:#fbbf24;"></span>
                        <span>Maintenance</span>
                    </div>
                    <span class="f-donut-legend-value">{{ $mnP }}% ({{ $avail['maintenance'] }})</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. ALT ENVANTER TABLOSU (TAM EKRAN GENİŞLİK - EKRANA RAHATÇA SIĞAR) --}}
<div class="f-card">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
        <div>
            <h2 style="font-size:15px; font-weight:800; letter-spacing:0.8px; color:#fff; text-transform:uppercase;">
                TOOL INVENTORY & STATUS
            </h2>
            <p style="font-size:11px; color:#8b9bb4; margin-top:2px;">
                Canlı stok, zimmetli personel ve raf/göz konumları
            </p>
        </div>

        <div style="min-width:260px;">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="🔍 Takım adı, seri no veya barkod ile ara..."
                   style="background:#111620; border:1px solid #263346; color:#fff; border-radius:8px; padding:8px 14px; font-size:12px; width:100%; outline:none;">
        </div>
    </div>

    <div class="f-table-container">
        <table class="f-custom-table">
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
                @forelse($this->inventoryTools as $tool)
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
                    <tr>
                        <td>
                            <span style="font-family:'JetBrains Mono',monospace; font-weight:700; color:#cbd5e1; font-size:12px;">
                                {{ $toolCode }}
                            </span>
                        </td>
                        <td>
                            <span style="font-weight:700; color:#fff; font-size:13px;">{{ $tool->name }}</span>
                        </td>
                        <td style="color:#cbd5e1; font-size:12px;">
                            {{ $tool->toolGroup?->name ?? ($tool->category ? \App\Models\Tool::CATEGORIES[$tool->category] ?? $tool->category : 'Genel') }}
                        </td>
                        <td>
                            <span style="font-family:'JetBrains Mono',monospace; color:#94a3b8; font-size:12px;">
                                {{ $tool->serial_no ?? ($tool->barcode ?? '—') }}
                            </span>
                        </td>
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
                        <td>
                            @if($tool->status === 'loaned')
                                <span style="font-weight:600; font-size:12px; color:{{ $isOverdue ? '#f87171' : '#cbd5e1' }};">
                                    {{ $returnDate }}
                                </span>
                            @else
                                <span style="color:#64748b;">-</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($tool->status === 'available')
                                <span class="f-status-badge f-status-available">Available</span>
                            @elseif($isOverdue)
                                <span class="f-status-badge f-status-overdue">Overdue</span>
                            @elseif($tool->status === 'loaned')
                                <span class="f-status-badge f-status-in-use">In Use</span>
                            @elseif($tool->status === 'maintenance')
                                <span class="f-status-badge f-status-maintenance">In Maintenance</span>
                            @else
                                <span class="f-status-badge f-status-maintenance">Scrapped</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:28px; color:#8b9bb4;">
                            Kayıtlı takım bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Sayfalama (Livewire Pagination) --}}
    <div style="margin-top:16px;">
        {{ $this->inventoryTools->links() }}
    </div>
</div>
</x-filament-panels::page>
