<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} — {{ $dateRange }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 12mm 12mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
        }

        /* ─── Header ─── */
        .page-header {
            background: #1e3a8a;
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 12px;
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
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header-subtitle {
            font-size: 8.5pt;
            opacity: 0.85;
            margin-top: 2px;
        }

        .header-meta {
            text-align: right;
            font-size: 8pt;
            line-height: 1.4;
        }

        .timestamp-badge {
            background: #2563eb;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
            margin-top: 3px;
        }

        /* ─── Stats Row ─── */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 12px;
        }

        .stat-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 10px;
            text-align: center;
        }

        .stat-box.primary { border-left: 4px solid #2563eb; }
        .stat-box.warning { border-left: 4px solid #f59e0b; }
        .stat-box.success { border-left: 4px solid #10b981; }
        .stat-box.danger  { border-left: 4px solid #ef4444; }

        .stat-val {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
        }
        .stat-lbl {
            font-size: 7.5pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* ─── Table ─── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .data-table th {
            background: #2563eb;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 6px;
            text-align: left;
            border: 1px solid #1d4ed8;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .data-table tbody tr.row-overdue {
            background: #fef2f2;
        }
        .data-table tbody tr.row-returned {
            background: #f0fdf4;
        }

        /* ─── Badges ─── */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
        }

        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        .mono {
            font-family: monospace;
            font-size: 8pt;
            color: #334155;
        }

        /* ─── Footer ─── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            border: none;
            padding: 0;
        }
    </style>
</head>
<body>

{{-- Header Banner --}}
<div class="page-header">
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="header-title">🔧 TAKIMHANE YÖNETİM SİSTEMİ</div>
                <div class="header-subtitle">{{ $title }} — Günlük Zimmet & İade Resmi Raporu</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="header-meta">
                    <b>Rapor Tarihi:</b> {{ $dateRange }}<br>
                    <div class="timestamp-badge">⏱️ Zaman Damgası: {{ now()->format('d.m.Y H:i:s') }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- Özet İstatistikler --}}
<table class="stats-table">
    <tr>
        <td class="stat-box primary" style="width: 25%;">
            <div class="stat-val">{{ $stats['total_loans'] }}</div>
            <div class="stat-lbl">Toplam Parça Hareketi</div>
        </td>
        <td class="stat-box warning" style="width: 25%;">
            <div class="stat-val">{{ $stats['active_loans'] }}</div>
            <div class="stat-lbl">Halen Ödünçte (Zimmette)</div>
        </td>
        <td class="stat-box success" style="width: 25%;">
            <div class="stat-val">{{ $stats['total_returns'] }}</div>
            <div class="stat-lbl">İade Edilen Parçalar</div>
        </td>
        <td class="stat-box danger" style="width: 25%;">
            <div class="stat-val">{{ $stats['overdue_count'] }}</div>
            <div class="stat-lbl">Gecikmiş Parçalar</div>
        </td>
    </tr>
</table>

{{-- Ana Tablo --}}
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%; text-align: center;">No</th>
            <th style="width: 18%;">Parça Adı</th>
            <th style="width: 11%;">Seri No</th>
            <th style="width: 11%;">Konum (Göz)</th>
            <th style="width: 14%;">Teslim Alan Personel</th>
            <th style="width: 8%;">Sicil No</th>
            <th style="width: 13%;">Teslim Zaman Damgası</th>
            <th style="width: 10%;">Plan. İade</th>
            <th style="width: 11%;">İade Durumu & Zamanı</th>
        </tr>
    </thead>
    <tbody>
        @forelse($loans as $index => $loan)
            @php
                $isOverdue = ($loan->status === 'overdue' || $loan->isOverdue());
                $isReturned = ($loan->status === 'returned');
            @endphp
            <tr class="{{ $isOverdue ? 'row-overdue' : ($isReturned ? 'row-returned' : '') }}">
                <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $index + 1 }}</td>
                <td>
                    <b>{{ $loan->tool?->name ?? 'Silinmiş Parça' }}</b>
                </td>
                <td class="mono">
                    {{ $loan->tool?->serial_no ?? '—' }}
                </td>
                <td style="font-size: 7.5pt; color: #475569;">
                    @if($loan->tool?->slot)
                        {{ $loan->tool->slot->shelf?->block?->name ?? '' }} / R{{ $loan->tool->slot->shelf?->shelf_number ?? '' }} / G{{ $loan->tool->slot->slot_number ?? '' }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    <b>{{ $loan->personnel?->name ?? ($loan->loanedByUser?->name ?? 'Bilinmiyor') }}</b>
                    <br><small style="color: #64748b; font-size: 7pt;">{{ $loan->personnel?->department ?? 'Genel' }}</small>
                </td>
                <td class="mono" style="text-align: center;">
                    {{ $loan->personnel?->badge_number ?? '—' }}
                </td>
                <td style="white-space: nowrap; font-size: 7.5pt;">
                    ⏱️ {{ $loan->loaned_at?->format('d.m.Y H:i:s') ?? '—' }}
                </td>
                <td style="white-space: nowrap; font-size: 7.5pt;">
                    {{ $loan->planned_return_at?->format('d.m.Y') ?? '—' }}
                </td>
                <td style="text-align: center; white-space: nowrap;">
                    @if($isReturned)
                        <span class="badge badge-success">
                            ✅ İade ({{ $loan->returned_at?->format('H:i:s') }})
                        </span>
                    @elseif($isOverdue)
                        <span class="badge badge-danger">
                            🚨 {{ $loan->overdue_days }}g Gecikme
                        </span>
                    @else
                        <span class="badge badge-warning">
                            ⏳ Ödünçte ({{ now()->diffInDays($loan->planned_return_at, false) }}g kaldı)
                        </span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #64748b;">
                    Bu tarih için herhangi bir zimmet veya iade hareketi bulunamadı.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Altbilgi --}}
<div class="page-footer">
    <table class="footer-table">
        <tr>
            <td style="text-align: left;">
                Takımhane Yönetim Sistemi &bull; Resmi Gün Sonu Zimmet Arşiv Raporu
            </td>
            <td style="text-align: right;">
                Toplam <b>{{ count($loans) }}</b> parça hareketi listelendi &bull; Sayfa 1 / 1
            </td>
        </tr>
    </table>
</div>

</body>
</html>
