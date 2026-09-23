@extends('admin-portal.layout')
@section('title', 'Yönetici Portalı Genel Bakış & Günlük Zimmet Takibi')

@section('content')
{{-- Karşılama & Hızlı İşlemler --}}
<div style="padding: 18px 16px 0;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg,#4f46e5,#4338ca); display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800; color:#fff; flex-shrink:0; box-shadow:0 4px 12px rgba(79,70,229,0.4);">
                {{ strtoupper(substr($user?->name ?? 'A', 0, 1)) }}
            </div>
            <div>
                <div style="font-size:18px; font-weight:800; color:#fff;">Merhaba, {{ explode(' ', $user?->name ?? 'Yetkili')[0] }}! 👋</div>
                <div style="font-size:12px; color:var(--muted); margin-top:2px;">
                    @if($isToday)
                        <span style="color:#34d399; font-weight:600;">● Canlı Günlük Takip</span> (Bugün: {{ now()->format('d.m.Y') }})
                    @else
                        <span style="color:#fbbf24; font-weight:600;">📂 Arşiv İncelemesi:</span> {{ $carbonDate->format('d.m.Y') }}
                    @endif
                </div>
            </div>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <button type="button" onclick="openAssignModal()" class="btn btn-success" style="padding:9px 16px; font-size:13px; font-weight:700;">
                ➕ Yeni Parça Zimmetle
            </button>
            <a href="{{ route('admin-portal.add-tool') }}" class="btn btn-ghost" style="padding:9px 14px; font-size:13px;">
                🔧 Yeni Alet Ekle
            </a>
        </div>
    </div>
</div>

{{-- TARİH SEÇİCİ & GÜN SONU ÇIKTI / ARŞİV ÇUBUĞU --}}
<div style="padding: 12px 16px 0;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:14px 16px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; box-shadow:0 4px 14px rgba(0,0,0,0.15);">
        {{-- Tarih Seçimi --}}
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span style="font-size:13px; font-weight:700; color:#cbd5e1;">📅 Arşiv Tarihi:</span>
            <input type="date" id="archive-date-picker" value="{{ $selectedDate }}" onchange="changeArchiveDate(this.value)"
                   style="background:var(--surface2); border:1px solid #475569; color:#fff; padding:7px 12px; border-radius:8px; font-size:13px; outline:none; font-family:inherit; font-weight:600; cursor:pointer;">
            
            @if(!$isToday)
                <a href="{{ route('admin-portal.index') }}" class="btn btn-ghost btn-sm" style="color:#818cf8; border-color:#6366f1; font-weight:700;">
                    ↺ Bugüne Dön
                </a>
            @endif
        </div>

        {{-- Gün Sonu Çıktı Butonları (A4 Yatay Formatında) --}}
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin-portal.export-daily-pdf', ['date' => $selectedDate]) }}" target="_blank"
               class="btn btn-danger btn-sm" style="font-weight:700; gap:6px;">
                📄 Gün Sonu PDF (A4 Yatay)
            </a>
            <a href="{{ route('admin-portal.export-daily-excel', ['date' => $selectedDate]) }}" target="_blank"
               class="btn btn-success btn-sm" style="font-weight:700; gap:6px;">
                📊 Gün Sonu Excel (A4 Yatay)
            </a>
        </div>
    </div>
</div>

@if(!$isToday)
    <div style="margin: 10px 16px 0;">
        <div class="alert alert-warn" style="margin:0; font-size:13px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
            <span>📂 <b>{{ $carbonDate->format('d.m.Y') }}</b> gününe ait arşiv kayıtları listeleniyor.</span>
            <a href="{{ route('admin-portal.index') }}" class="btn btn-warn btn-xs" style="font-weight:700;">Bugünkü Canlı Duruma Dön</a>
        </div>
    </div>
@endif

{{-- Günlük Hareket İstatistikleri Grid --}}
<div style="padding: 12px 16px; display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:10px;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:12px 14px; border-left:4px solid #6366f1;">
        <div style="font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase;">Günün Hareketi</div>
        <div style="font-size:22px; font-weight:800; color:#fff; margin-top:2px;" id="stat-total-movements">{{ $dailyStats['total_movements'] }}</div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:12px 14px; border-left:4px solid #f59e0b;">
        <div style="font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase;">Halen Dışarıda</div>
        <div style="font-size:22px; font-weight:800; color:#fbbf24; margin-top:2px;" id="stat-still-loaned">{{ $dailyStats['still_loaned'] }}</div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:12px 14px; border-left:4px solid #10b981;">
        <div style="font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase;">İade Alınan</div>
        <div style="font-size:22px; font-weight:800; color:#34d399; margin-top:2px;" id="stat-returned-today">{{ $dailyStats['returned_today'] }}</div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:12px 14px; border-left:4px solid {{ $dailyStats['overdue_count'] > 0 ? '#ef4444' : '#64748b' }};">
        <div style="font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase;">Gecikmiş</div>
        <div style="font-size:22px; font-weight:800; color:{{ $dailyStats['overdue_count'] > 0 ? '#f87171' : '#94a3b8' }}; margin-top:2px;">{{ $dailyStats['overdue_count'] }}</div>
    </div>
</div>

{{-- Canlı Bildirim Alanı --}}
<div id="live-alert" class="alert hidden" style="margin: 0 16px 12px 16px;"></div>

{{-- GÜNLÜK ZİMMET & İADE HAREKETLERİ TABLOSU --}}
<div class="card" style="margin: 0 16px 16px 16px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
        <div>
            <div class="card-title" style="margin-bottom:2px; font-size:14px; color:#fff;">
                @if($isToday)
                    📋 Günlük Zimmet & İade Hareketleri Tablosu
                @else
                    📋 {{ $carbonDate->format('d.m.Y') }} Tarihli Zimmet Dökümü
                @endif
            </div>
            <p style="font-size:12px; color:var(--muted);">Tüm teslim ve iadeler tam zaman damgasıyla listelenir</p>
        </div>
        
        {{-- Durum Filtresi Butonları --}}
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            <button type="button" class="btn btn-sm filter-tab active" onclick="setFilterStatus('all', this)" style="background:var(--primary); color:#fff; font-weight:700;">
                Tümü ({{ $dailyStats['total_movements'] }})
            </button>
            <button type="button" class="btn btn-sm filter-tab" onclick="setFilterStatus('active', this)" style="background:var(--surface2); color:#cbd5e1;">
                🔧 Dışarıda ({{ $dailyStats['still_loaned'] }})
            </button>
            <button type="button" class="btn btn-sm filter-tab" onclick="setFilterStatus('returned', this)" style="background:var(--surface2); color:#cbd5e1;">
                ✅ İade Edilen ({{ $dailyStats['returned_today'] }})
            </button>
        </div>
    </div>

    {{-- Canlı Filtreleme Arama Kutusu --}}
    @if(count($groupedLoans) > 0)
        <div style="margin-bottom:16px;">
            <input type="text" id="loan-search-input" onkeyup="filterLoanList()"
                   placeholder="🔍 Personel adı, sicil no, alet veya seri no ile anında ara..."
                   class="form-input" style="font-size:14px; padding:12px 14px; border-radius:10px; background:var(--surface2); border:1px solid #475569;">
        </div>
    @endif

    {{-- Gruplanmış Personel Kartları --}}
    <div id="grouped-loans-container">
        @forelse($groupedLoans as $group)
            <div class="person-loan-card" style="background:var(--surface2); border:1px solid {{ $group['has_overdue'] ? 'rgba(239,68,68,0.5)' : 'var(--border)' }}; border-radius:14px; padding:14px; margin-bottom:14px; box-shadow:0 2px 8px rgba(0,0,0,0.15);"
                 data-person-name="{{ strtolower($group['personnel_name']) }}"
                 data-search-text="{{ strtolower($group['personnel_name'] . ' ' . $group['personnel_dept'] . ' ' . $group['badge_number'] . ' ' . $group['loans']->pluck('tool.name')->join(' ') . ' ' . $group['loans']->pluck('tool.serial_no')->join(' ')) }}">
                
                {{-- Personel Başlık Bilgisi --}}
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.08); flex-wrap:wrap; gap:10px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:38px; height:38px; border-radius:10px; background:{{ $group['has_overdue'] ? 'rgba(239,68,68,0.25)' : 'rgba(79,70,229,0.25)' }}; color:{{ $group['has_overdue'] ? '#ef4444' : '#818cf8' }}; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px;">
                            👤
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:15px; color:#fff; display:flex; align-items:center; gap:6px;">
                                <span>{{ $group['personnel_name'] }}</span>
                                @if($group['badge_number'] !== '—')
                                    <span style="font-family:monospace; font-size:11px; background:rgba(255,255,255,0.12); padding:2px 7px; border-radius:4px; color:#e2e8f0; font-weight:600;">
                                        Sicil: {{ $group['badge_number'] }}
                                    </span>
                                @endif
                            </div>
                            <div style="font-size:12px; color:var(--muted); margin-top:1px;">
                                {{ $group['personnel_dept'] }}
                            </div>
                        </div>
                    </div>
                    
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        @if($group['has_overdue'])
                            <span class="badge badge-overdue" style="font-size:11px; padding:4px 10px;">
                                🚨 Gecikmiş Parça Var
                            </span>
                        @endif
                        <span class="badge" style="font-size:11px; padding:4px 10px; background:rgba(255,255,255,0.1); color:#fff; border:1px solid rgba(255,255,255,0.15);">
                            Toplam {{ $group['total_count'] }} Parça ({{ $group['active_count'] }} Aktif, {{ $group['returned_count'] }} İade)
                        </span>

                        {{-- Bu Kişiye Parça Ver Butonu --}}
                        @if($group['personnel_id'])
                            <button type="button" onclick="openAssignModal({{ $group['personnel_id'] }}, '{{ addslashes($group['personnel_name']) }}')"
                                    class="btn btn-primary btn-xs" style="font-weight:700; gap:4px;">
                                ➕ Parça Ver
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Personelin Elindeki Aletlerin Tablosu --}}
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="color:#94a3b8; border-bottom:1px solid rgba(255,255,255,0.1); font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:8px 6px;">Alet & Seri No</th>
                                <th style="padding:8px 6px;">Konum (Göz)</th>
                                <th style="padding:8px 6px;">Teslim Zaman Damgası</th>
                                <th style="padding:8px 6px;">İade Durumu & Zamanı</th>
                                <th style="padding:8px 6px; text-align:right;">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['loans'] as $loan)
                                <tr id="loan-row-{{ $loan->id }}" class="loan-row-item {{ $loan->status === 'returned' ? 'status-returned' : 'status-active' }}"
                                    data-status="{{ $loan->status }}"
                                    style="border-bottom:1px solid rgba(255,255,255,0.05); {{ $loan->status === 'returned' ? 'opacity:0.85;' : '' }}">
                                    
                                    {{-- Alet Adı ve Seri No (Resim Önizlemeli) --}}
                                    <td style="padding:10px 6px; vertical-align:middle;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            @if($loan->tool?->image)
                                                <img src="{{ Storage::url($loan->tool->image) }}" alt="{{ $loan->tool->name }}"
                                                     onclick="openImageLightbox('{{ Storage::url($loan->tool->image) }}', '{{ addslashes($loan->tool->name) }}')"
                                                     style="width:40px; height:40px; object-fit:cover; border-radius:8px; cursor:pointer; border:1px solid rgba(255,255,255,0.15); transition:transform .2s;"
                                                     onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'"
                                                     title="Resmi büyütmek için tıklayın">
                                            @endif
                                            <div>
                                                <div style="font-weight:700; color:#fff; font-size:13px;">
                                                    {{ $loan->tool?->name ?? 'Silinmiş Alet' }}
                                                </div>
                                                <div style="font-family:monospace; font-size:11px; color:#818cf8; margin-top:2px;">
                                                    SN: {{ $loan->tool?->serial_no ?? 'Yok' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Bulunduğu Konum --}}
                                    <td style="padding:10px 6px; vertical-align:middle; color:#cbd5e1; font-size:12px;">
                                        @if($loan->tool?->slot)
                                            <span style="background:rgba(255,255,255,0.06); padding:3px 6px; border-radius:4px; font-family:monospace; font-size:11px;">
                                                📍 {{ $loan->tool->slot->shelf?->block?->name ?? '' }} / R{{ $loan->tool->slot->shelf?->shelf_number ?? '' }} / G{{ $loan->tool->slot->slot_number ?? '' }}
                                            </span>
                                        @else
                                            <span style="color:#64748b;">—</span>
                                        @endif
                                    </td>

                                    {{-- Teslim Zaman Damgası (Saniyeli) --}}
                                    <td style="padding:10px 6px; vertical-align:middle; color:#cbd5e1; font-size:12px; white-space:nowrap;">
                                        ⏱️ {{ $loan->loaned_at?->format('d.m.Y H:i:s') ?? '-' }}
                                    </td>

                                    {{-- İade & Gecikme Durumu --}}
                                    <td class="status-cell" style="padding:10px 6px; vertical-align:middle; white-space:nowrap;">
                                        @if($loan->status === 'returned')
                                            <span class="badge badge-active" style="font-size:11px; padding:3px 8px; background:rgba(16,185,129,0.2); color:#34d399; border:1px solid rgba(16,185,129,0.4);">
                                                ✅ İade Edildi ({{ $loan->returned_at?->format('H:i:s') }})
                                            </span>
                                        @elseif($loan->isOverdue() || $loan->status === 'overdue')
                                            <span class="badge badge-overdue" style="font-size:11px; padding:3px 8px;">
                                                🚨 {{ $loan->overdue_days }}g Gecikti ({{ $loan->planned_return_at?->format('d.m') }})
                                            </span>
                                        @else
                                            <span class="badge badge-warn" style="font-size:11px; padding:3px 8px;">
                                                ⏳ {{ now()->diffInDays($loan->planned_return_at, false) }}g kaldı ({{ $loan->planned_return_at?->format('d.m') }})
                                            </span>
                                        @endif
                                    </td>

                                    {{-- İade Al Butonu / Durum --}}
                                    <td class="action-cell" style="padding:10px 6px; vertical-align:middle; text-align:right;">
                                        @if($loan->status !== 'returned')
                                            <button type="button" class="btn btn-success btn-sm return-btn"
                                                    onclick="returnSingleLoan({{ $loan->id }}, '{{ addslashes($loan->tool?->name ?? 'Alet') }}', '{{ addslashes($group['personnel_name']) }}')"
                                                    style="padding:6px 14px; font-weight:700; font-size:12px;">
                                                ↩️ İade Al
                                            </button>
                                        @else
                                            <span style="font-size:12px; color:#34d399; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                                                ✓ Teslim Alındı
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div id="empty-loans-state" style="text-align:center; padding:36px 16px; background:var(--surface2); border-radius:14px; border:1px dashed var(--border);">
                <div style="font-size:44px; margin-bottom:10px;">✅</div>
                <div style="font-size:16px; font-weight:700; color:#fff;">
                    @if($isToday)
                        Bugün henüz bir parça hareketi bulunmuyor.
                    @else
                        Bu tarihte ({{ $carbonDate->format('d.m.Y') }}) herhangi bir zimmet hareketi bulunamadı.
                    @endif
                </div>
                <div style="font-size:13px; color:var(--muted); margin-top:6px;">Yeni parça zimmetlemek için üstteki butonu kullanabilirsiniz.</div>
            </div>
        @endforelse
    </div>
</div>

{{-- HIZLI PARÇA ZİMMETLEME MODALI --}}
<div id="assign-modal" class="hidden" style="position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:999; display:flex; align-items:center; justify-content:center; padding:16px;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:18px; width:100%; max-width:500px; padding:22px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px -12px rgba(0,0,0,0.6);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid var(--border); padding-bottom:14px;">
            <div style="font-weight:800; font-size:17px; color:#fff; display:flex; align-items:center; gap:8px;">
                <span>➕</span>
                <span>Zaman Damgalı Parça Zimmetle</span>
            </div>
            <button onclick="closeAssignModal()" style="background:transparent; border:none; color:var(--muted); font-size:22px; cursor:pointer; padding:4px;">✕</button>
        </div>

        <form id="assign-tool-form" onsubmit="submitAssignForm(event)">
            {{-- Personel Seçimi --}}
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label" for="assign-personnel-id">👤 Personel Seçin</label>
                <select class="form-input" id="assign-personnel-id" name="personnel_id" required style="font-size:13px;">
                    <option value="">-- Personel Seçin --</option>
                    @foreach($personnelList as $person)
                        <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->badge_number }} - {{ $person->department ?? 'Genel' }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Parça Seçimi --}}
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label" for="assign-tool-id">🔧 Parça / Alet Seçin (Sadece Müsait Olanlar)</label>
                <select class="form-input" id="assign-tool-id" name="tool_id" required style="font-size:13px;">
                    <option value="">-- Parça Seçin ({{ count($availableTools) }} adet hazır) --</option>
                    @foreach($availableTools as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->name }} [SN: {{ $t->serial_no ?? 'Yok' }}]
                            @if($t->slot) - ({{ $t->slot->shelf?->block?->name ?? '' }} / R{{ $t->slot->shelf?->shelf_number ?? '' }} / G{{ $t->slot->slot_number ?? '' }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Planlanan İade Süresi --}}
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label" for="assign-days">⏳ İade Süresi (Gün)</label>
                <input class="form-input" type="number" id="assign-days" name="days" value="7" min="1" max="90" required style="font-size:14px;">
                <small style="color:var(--muted); font-size:11px; display:block; margin-top:4px;">Örn: 7 gün sonra iade beklenir.</small>
            </div>

            {{-- Notlar --}}
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" for="assign-notes">📝 Zimmet Notu (İsteğe bağlı)</label>
                <textarea class="form-input" id="assign-notes" name="notes" rows="2" placeholder="İş emri, proje veya kullanım amacı..." style="font-size:13px; resize:none;"></textarea>
            </div>

            {{-- Zaman Damgası Bilgisi --}}
            <div style="background:rgba(79,70,229,0.15); border:1px solid rgba(79,70,229,0.35); border-radius:10px; padding:12px; margin-bottom:18px; font-size:12px; color:#c7d2fe;">
                ⏱️ <b>Zaman Damgası:</b> Parça kaydedildiği anda tam saniyesiyle sisteme mühürlenir.
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeAssignModal()" class="btn btn-ghost" style="padding:10px 18px;">İptal</button>
                <button type="submit" id="assign-submit-btn" class="btn btn-success" style="padding:10px 22px; font-weight:700;">💾 Zimmeti Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var currentStatusFilter = 'all';

// ═══════════════════════════════════════════════════════════════
// TARİH DEĞİŞTİRME & ARŞİVE GİTME
// ═══════════════════════════════════════════════════════════════
function changeArchiveDate(dateStr) {
    if (!dateStr) return;
    window.location.href = "{{ route('admin-portal.index') }}?date=" + dateStr;
}

// ═══════════════════════════════════════════════════════════════
// DURUM FİLTRESİ (Tümü / Dışarıda / İade Edilen)
// ═══════════════════════════════════════════════════════════════
function setFilterStatus(status, btn) {
    currentStatusFilter = status;
    document.querySelectorAll('.filter-tab').forEach(function(el) {
        el.style.background = 'var(--surface2)';
        el.style.color = '#cbd5e1';
    });
    if (btn) {
        btn.style.background = 'var(--primary)';
        btn.style.color = '#fff';
    }
    filterLoanList();
}

function filterLoanList() {
    var searchInput = document.getElementById('loan-search-input');
    var filterText = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var cards = document.querySelectorAll('.person-loan-card');

    cards.forEach(function(card) {
        var text = card.getAttribute('data-search-text') || '';
        var rows = card.querySelectorAll('.loan-row-item');
        var visibleRowCount = 0;

        rows.forEach(function(row) {
            var rowStatus = row.getAttribute('data-status');
            var matchesStatus = (currentStatusFilter === 'all')
                || (currentStatusFilter === 'active' && rowStatus !== 'returned')
                || (currentStatusFilter === 'returned' && rowStatus === 'returned');

            if (matchesStatus) {
                row.style.display = '';
                visibleRowCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var matchesText = text.includes(filterText) || filterText === '';

        if (matchesText && visibleRowCount > 0) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// ═══════════════════════════════════════════════════════════════
// ZİMMET VERME MODAL KONTROLLERİ
// ═══════════════════════════════════════════════════════════════
function openAssignModal(personnelId, personnelName) {
    var modal = document.getElementById('assign-modal');
    modal.classList.remove('hidden');
    if (personnelId) {
        document.getElementById('assign-personnel-id').value = personnelId;
    }
}

function closeAssignModal() {
    document.getElementById('assign-modal').classList.add('hidden');
}

async function submitAssignForm(event) {
    event.preventDefault();
    var form = document.getElementById('assign-tool-form');
    var btn = document.getElementById('assign-submit-btn');
    var formData = new FormData(form);

    btn.disabled = true;
    btn.textContent = '⏳ Kaydediliyor...';
    btn.style.opacity = '0.6';

    try {
        var response = await fetch("{{ route('admin-portal.assign-loan') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        var data = await response.json();

        if (!response.ok || !data.success) {
            alert('Hata: ' + (data.message || 'Zimmet kaydedilemedi.'));
            btn.disabled = false;
            btn.textContent = '💾 Zimmeti Kaydet';
            btn.style.opacity = '1';
            return;
        }

        showAlert(data.message, 'success');
        closeAssignModal();
        form.reset();

        setTimeout(function() {
            window.location.reload();
        }, 800);

    } catch(e) {
        alert('Bağlantı hatası: ' + e.message);
        btn.disabled = false;
        btn.textContent = '💾 Zimmeti Kaydet';
        btn.style.opacity = '1';
    }
}

// ═══════════════════════════════════════════════════════════════
// AJAX İLE ANINDA İADE ALMA FONKSİYONU
// ═══════════════════════════════════════════════════════════════
async function returnSingleLoan(loanId, toolName, personnelName) {
    if (!confirm(`"${toolName}" adlı parçayı ${personnelName} adlı personelden teslim almak (iade) istiyor musunuz?`)) {
        return;
    }

    var row = document.getElementById('loan-row-' + loanId);
    var btn = row ? row.querySelector('.return-btn') : null;
    if (btn) {
        btn.disabled = true;
        btn.textContent = '⏳ İade Alınıyor...';
        btn.style.opacity = '0.6';
    }

    try {
        var response = await fetch(`/admin-portal/loans/${loanId}/return`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notes: 'Yönetici ana sayfasından hızlı iade alındı.' })
        });

        var data = await response.json();

        if (!response.ok || !data.success) {
            alert('Hata: ' + (data.message || 'İade işlemi yapılamadı.'));
            if (btn) {
                btn.disabled = false;
                btn.textContent = '↩️ İade Al';
                btn.style.opacity = '1';
            }
            return;
        }

        showAlert(data.message || `✅ "${toolName}" başarıyla iade alındı.`, 'success');

        // Satırı silmek yerine İADE EDİLDİ durumuna dönüştür
        if (row) {
            var nowTime = new Date().toLocaleTimeString('tr-TR');
            row.setAttribute('data-status', 'returned');
            row.className = 'loan-row-item status-returned';
            row.style.background = 'rgba(16,185,129,0.15)';
            row.style.opacity = '0.85';

            var statusCell = row.querySelector('.status-cell');
            if (statusCell) {
                statusCell.innerHTML = `
                    <span class="badge badge-active" style="font-size:11px; padding:3px 8px; background:rgba(16,185,129,0.2); color:#34d399; border:1px solid rgba(16,185,129,0.4);">
                        ✅ İade Edildi (${nowTime})
                    </span>
                `;
            }

            var actionCell = row.querySelector('.action-cell');
            if (actionCell) {
                actionCell.innerHTML = `<span style="font-size:12px; color:#34d399; font-weight:700;">✓ Teslim Alındı</span>`;
            }

            // Sayaçları güncelle
            var stillLoanedEl = document.getElementById('stat-still-loaned');
            if (stillLoanedEl) {
                var cur = parseInt(stillLoanedEl.textContent) || 1;
                stillLoanedEl.textContent = Math.max(0, cur - 1);
            }
            var returnedTodayEl = document.getElementById('stat-returned-today');
            if (returnedTodayEl) {
                var curRet = parseInt(returnedTodayEl.textContent) || 0;
                returnedTodayEl.textContent = curRet + 1;
            }
        }

    } catch (e) {
        alert('Bağlantı hatası: ' + e.message);
        if (btn) {
            btn.disabled = false;
            btn.textContent = '↩️ İade Al';
            btn.style.opacity = '1';
        }
    }
}

function showAlert(message, type) {
    var alertEl = document.getElementById('live-alert');
    if (!alertEl) return;
    alertEl.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
    alertEl.textContent = message;
    alertEl.classList.remove('hidden');
    setTimeout(function() {
        alertEl.classList.add('hidden');
    }, 4000);
}
</script>
@endpush
