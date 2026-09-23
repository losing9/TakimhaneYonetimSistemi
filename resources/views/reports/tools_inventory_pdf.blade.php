<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Takımhane — Parça & Envanter Listesi ({{ now()->format('d.m.Y') }})</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
        }

        /* ─── Header ─── */
        .page-header {
            background: #0f172a;
            color: #ffffff;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
            color: #fff;
        }

        .header-title {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header-subtitle {
            font-size: 8pt;
            opacity: 0.85;
            margin-top: 2px;
        }

        .header-meta {
            text-align: right;
            font-size: 7.5pt;
            line-height: 1.3;
        }

        .timestamp-badge {
            background: #d97706;
            padding: 2px 7px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
            margin-top: 2px;
        }

        /* ─── Stats Row ─── */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 8px;
        }

        .stat-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.1;
        }

        .stat-label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }

        .stat-total       .stat-value { color: #0f172a; }
        .stat-available   .stat-value { color: #16a34a; }
        .stat-loaned      .stat-value { color: #d97706; }
        .stat-maintenance .stat-value { color: #0284c7; }
        .stat-scrapped    .stat-value { color: #dc2626; }

        /* ─── Data Table ─── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        .data-table th {
            background: #d97706;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            text-align: left;
            border: 1px solid #b45309;
            font-size: 7.5pt;
        }

        .data-table th.center,
        .data-table td.center {
            text-align: center;
        }

        .data-table td {
            padding: 4px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .row-loaned {
            background: #fffbeb !important;
        }

        .row-maintenance {
            background: #f0f9ff !important;
        }

        .row-scrapped {
            background: #fef2f2 !important;
        }

        /* ─── Status Badges ─── */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .badge-available   { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-loaned      { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-maintenance { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-scrapped    { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        .serial-text {
            font-family: monospace;
            font-size: 7pt;
            color: #334155;
            font-weight: bold;
        }

        .borrower-info {
            font-size: 7pt;
            color: #b45309;
            font-weight: bold;
        }

        /* ─── Footer ─── */
        .footer-note {
            margin-top: 8px;
            font-size: 6.5pt;
            color: #94a3b8;
            text-align: right;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    {{-- Üst Başlık Banner --}}
    <div class="page-header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">🔧 TAKIMHANE YÖNETİM SİSTEMİ</div>
                    <div class="header-subtitle">Genel Parça & Envanter Listesi (Veritabanı Çıktısı / Yedek)</div>
                </td>
                <td class="header-meta">
                    <div>Rapor Tarihi: <strong>{{ now()->format('d.m.Y') }}</strong></div>
                    <div class="timestamp-badge">Zaman Damgası: {{ now()->format('d.m.Y H:i:s') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- İstatistikler --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box stat-total">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Toplam Parça</div>
            </td>
            <td class="stat-box stat-available">
                <div class="stat-value">{{ $stats['available'] }}</div>
                <div class="stat-label">✅ Mevcut</div>
            </td>
            <td class="stat-box stat-loaned">
                <div class="stat-value">{{ $stats['loaned'] }}</div>
                <div class="stat-label">📤 Ödünçte</div>
            </td>
            <td class="stat-box stat-maintenance">
                <div class="stat-value">{{ $stats['maintenance'] }}</div>
                <div class="stat-label">🔧 Bakımda</div>
            </td>
            <td class="stat-box stat-scrapped">
                <div class="stat-value">{{ $stats['scrapped'] }}</div>
                <div class="stat-label">🗑️ Hurda</div>
            </td>
        </tr>
    </table>

    {{-- Parça Tablosu --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="center">#</th>
                <th style="width: 170px;">Parça Adı</th>
                <th style="width: 85px;">Kategori</th>
                <th style="width: 85px;">Seri No</th>
                <th style="width: 75px;">Barkod / QR</th>
                <th style="width: 120px;">Konum (Göz / Raf / Blok)</th>
                <th style="width: 60px;" class="center">Durum</th>
                <th>Zimmet Durumu (Kimin Üzerinde)</th>
                <th style="width: 60px;" class="center">Maks. Gün</th>
                <th style="width: 75px;" class="center">Eklenme Tarihi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tools as $i => $tool)
                @php
                    $rowClass = match($tool->status) {
                        'loaned'      => 'row-loaned',
                        'maintenance' => 'row-maintenance',
                        'scrapped'    => 'row-scrapped',
                        default       => '',
                    };
                    $badgeClass = match($tool->status) {
                        'available'   => 'badge-available',
                        'loaned'      => 'badge-loaned',
                        'maintenance' => 'badge-maintenance',
                        'scrapped'    => 'badge-scrapped',
                        default       => 'badge-available',
                    };
                    $statusLabel = match($tool->status) {
                        'available'   => 'Mevcut',
                        'loaned'      => 'Ödünçte',
                        'maintenance' => 'Bakımda',
                        'scrapped'    => 'Hurda',
                        default       => $tool->status,
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="center">{{ $i + 1 }}</td>
                    <td><strong>{{ $tool->name }}</strong></td>
                    <td>{{ $tool->category_label ?? '—' }}</td>
                    <td class="serial-text">{{ $tool->serial_no ?? '—' }}</td>
                    <td style="font-family: monospace; font-size: 6.5pt;">{{ $tool->barcode ?? '—' }}</td>
                    <td>{{ $tool->location_label ?? '—' }}</td>
                    <td class="center">
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        @if($tool->status === 'loaned' && $tool->activeLoan)
                            <span class="borrower-info">
                                👤 {{ $tool->activeLoan->personnel?->name ?? '—' }}
                                @if($tool->activeLoan->personnel?->badge_number)
                                    ({{ $tool->activeLoan->personnel->badge_number }})
                                @endif
                            </span>
                            <div style="font-size: 6pt; color: #64748b;">
                                Veriliş: {{ $tool->activeLoan->loaned_at?->format('d.m.Y') }} • Plan. İade: {{ $tool->activeLoan->planned_return_at?->format('d.m.Y') }}
                            </div>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td class="center">{{ $tool->max_loan_days ?? 7 }} gün</td>
                    <td class="center">{{ $tool->created_at?->format('d.m.Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center" style="padding: 20px; color: #94a3b8;">Kayıtlı alet / parça bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Takımhane Yönetim Sistemi &bull; Bu rapor veritabanı güvenliği ve envanter denetimi amacıyla otomatik üretilmiştir. &bull; Sayfa 1/1
    </div>

</body>
</html>
