<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Personel Kimlik Kartı</title>
<style>
    @page {
        margin: 0;
    }
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
    }
    body { 
        font-family: 'DejaVu Sans', Arial, sans-serif; 
        background: #fff; 
        color: #000;
        margin: 0;
        padding: 0;
    }
    .card-page {
        width: 280pt;
        height: 180pt;
        page-break-after: always;
        overflow: hidden;
        border: 1.5pt solid #7c3aed;
        border-radius: 6pt;
        padding: 6pt;
        box-sizing: border-box;
    }
    .card-page:last-child {
        page-break-after: avoid;
    }
    table {
        width: 100%;
        height: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    td {
        vertical-align: middle;
        padding: 0;
    }
    .header-row {
        height: 28pt;
    }
    .header-cell {
        background: #7c3aed;
        color: #fff;
        padding: 2pt 6pt;
        border-radius: 3pt;
    }
    .header-title {
        font-size: 7.5pt;
        font-weight: bold;
        letter-spacing: 0.5pt;
        opacity: 0.9;
    }
    .org-name {
        font-size: 9pt;
        font-weight: bold;
    }
    .qr-col {
        width: 75pt;
        text-align: center;
        padding-top: 4pt;
    }
    .qr-img {
        width: 65pt;
        height: 65pt;
        display: block;
        margin: 0 auto;
    }
    .info-col {
        width: 193pt;
        padding-left: 8pt;
        padding-top: 4pt;
    }
    .badge-number {
        background: #ede9fe;
        color: #4f46e5;
        font-weight: bold;
        font-size: 8pt;
        padding: 1.5pt 5pt;
        border-radius: 3pt;
        display: inline-block;
        margin-bottom: 2pt;
    }
    .personnel-name {
        font-size: 10.5pt;
        font-weight: bold;
        color: #1e293b;
        margin-bottom: 2pt;
    }
    .info-row {
        font-size: 7.5pt;
        color: #475569;
        margin-bottom: 1pt;
    }
    .footer-row {
        height: 14pt;
    }
    .footer-cell {
        background: #f5f3ff;
        border-top: 1px solid #ede9fe;
        padding: 2pt;
        font-size: 7pt;
        color: #6d28d9;
        text-align: center;
        border-radius: 2pt;
    }
</style>
</head>
<body>
@foreach($personnelList as $person)
<div class="card-page">
    <table>
        <tr class="header-row">
            <td colspan="2" class="header-cell">
                <div class="header-title">👷 PERSONEL KİMLİK KARTI</div>
                <div class="org-name">Takımhane YS</div>
            </td>
        </tr>
        <tr>
            <td class="qr-col">
                <img class="qr-img" src="{{ $qrCodes[$person->id] }}" alt="QR">
            </td>
            <td class="info-col">
                <div>
                    <span class="badge-number">{{ $person->badge_number }}</span>
                </div>
                <div class="personnel-name">{{ $person->name }}</div>
                @if($person->department)
                <div class="info-row">🏢 {{ $person->department }}</div>
                @endif
                @if($person->email)
                <div class="info-row">✉️ {{ $person->email }}</div>
                @endif
                @if($person->phone)
                <div class="info-row">📱 {{ $person->phone }}</div>
                @endif
            </td>
        </tr>
        <tr class="footer-row">
            <td colspan="2" class="footer-cell">
                Bu kart takımhane zimmet işlemleri için kullanılır.
            </td>
        </tr>
    </table>
</div>
@endforeach
</body>
</html>
