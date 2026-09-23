<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Raf Etiketi</title>
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
    .label-page {
        width: 240pt;
        height: 160pt;
        page-break-after: always;
        overflow: hidden;
        border: 1.5pt solid #059669;
        border-radius: 4pt;
        padding: 6pt;
        box-sizing: border-box;
    }
    .label-page:last-child {
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
        height: 16pt;
    }
    .header-cell {
        background: #059669;
        color: #fff;
        font-size: 7.5pt;
        font-weight: bold;
        text-align: center;
        letter-spacing: 1px;
        border-radius: 2pt;
    }
    .qr-col {
        width: 70pt;
        text-align: center;
        padding-top: 4pt;
    }
    .qr-img {
        width: 62pt;
        height: 62pt;
        display: block;
        margin: 0 auto;
    }
    .info-col {
        width: 158pt;
        padding-left: 6pt;
        padding-top: 4pt;
    }
    .slot-id {
        background: #d1fae5;
        color: #065f46;
        font-size: 7pt;
        font-weight: bold;
        padding: 1.5pt 4pt;
        border-radius: 2pt;
        display: inline-block;
        margin-bottom: 3pt;
    }
    .slot-path {
        font-size: 9pt;
        font-weight: bold;
        color: #065f46;
        line-height: 1.3;
        margin-bottom: 3pt;
    }
    .capacity {
        font-size: 7pt;
        color: #4b5563;
    }
</style>
</head>
<body>
@foreach($slots as $slot)
<div class="label-page">
    <table>
        <tr class="header-row">
            <td colspan="2" class="header-cell">
                📦 RAF / GÖZ ETİKETİ
            </td>
        </tr>
        <tr>
            <td class="qr-col">
                <img class="qr-img" src="{{ $qrCodes[$slot->id] }}" alt="QR">
            </td>
            <td class="info-col">
                <div>
                    <span class="slot-id">ID: {{ $slot->id }}</span>
                </div>
                <div class="slot-path">
                    {{ $slot->shelf->block->name }}<br>
                    ▸ {{ $slot->shelf->name }}<br>
                    ▸ {{ $slot->name }}
                </div>
                <div class="capacity">
                    ⬜ Kapasite: {{ $slot->capacity }} parça
                </div>
            </td>
        </tr>
    </table>
</div>
@endforeach
</body>
</html>
