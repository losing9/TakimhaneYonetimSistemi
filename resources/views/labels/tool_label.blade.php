<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Takim Etiketi</title>
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
        width: 170pt;
        height: 85pt;
        page-break-after: always;
        overflow: hidden;
        padding: 4pt 6pt;
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
    .qr-col {
        width: 62pt;
        text-align: left;
    }
    .qr-img {
        width: 58pt;
        height: 58pt;
        display: block;
    }
    .info-col {
        width: 96pt;
        padding-left: 2pt;
    }
    .tool-name {
        font-size: 7pt;
        font-weight: bold;
        color: #0f172a;
        line-height: 1.15;
        height: 24pt;
        overflow: hidden;
        margin-bottom: 2pt;
    }
    .serial-lbl {
        font-size: 4.5pt;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.3pt;
        margin-bottom: 1pt;
    }
    .serial-val {
        font-size: 7.5pt;
        font-weight: bold;
        color: #1e40af;
        background: #eff6ff;
        border: 0.5pt solid #bfdbfe;
        border-radius: 2pt;
        padding: 1.5pt 3pt;
        display: inline-block;
        letter-spacing: 0.3pt;
    }
</style>
</head>
<body>
@foreach($tools as $tool)
<div class="label-page">
    <table>
        <tr>
            <td class="qr-col">
                <img class="qr-img" src="{{ $qrCodes[$tool->id] }}" alt="QR">
            </td>
            <td class="info-col">
                <div class="tool-name">{{ $tool->name }}</div>
                <div class="serial-lbl">Seri No</div>
                <div>
                    <span class="serial-val">{{ $tool->serial_no ?? '—' }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>
@endforeach
</body>
</html>
