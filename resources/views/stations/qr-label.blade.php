<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>İade İstasyonu QR - {{ $station->name }}</title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    body {
        font-family: Arial, sans-serif;
        background: #fff;
        color: #0f172a;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .page {
        width: 148mm;
        height: 210mm;
        margin: 0 auto;
        padding: 12mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-sizing: border-box;
        background: #fff;
    }
    .card {
        border: 3mm solid #1e40af;
        border-radius: 8mm;
        padding: 8mm;
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #fff;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    .header {
        font-size: 11pt;
        font-weight: 800;
        color: #1e40af;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 2mm;
    }
    .title-area {
        margin-bottom: 4mm;
    }
    h1 {
        font-size: 20pt;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .loc {
        font-size: 11pt;
        color: #475569;
        margin-top: 2mm;
        font-weight: 500;
    }
    .qr-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 4mm 0;
    }
    .qr-img {
        width: 60mm;
        height: 60mm;
        display: block;
        border: 1px solid #e2e8f0;
        border-radius: 4mm;
        padding: 2mm;
        background: #fff;
    }
    .token {
        font-size: 9pt;
        font-family: monospace;
        background: #f1f5f9;
        color: #334155;
        padding: 2mm 4mm;
        border-radius: 2mm;
        word-break: break-all;
        margin-bottom: 4mm;
        display: inline-block;
        border: 1px solid #e2e8f0;
    }
    .steps {
        font-size: 10.5pt;
        text-align: left;
        line-height: 1.6;
        color: #1e293b;
        background: #f8fafc;
        padding: 5mm 6mm;
        border-radius: 4mm;
        border-left: 1.5mm solid #1e40af;
    }
    .steps-title {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 2mm;
        font-size: 11pt;
        text-transform: uppercase;
    }
    .steps ol {
        margin-left: 5mm;
    }
    .steps li {
        margin-bottom: 1mm;
    }
    .warn {
        font-size: 9pt;
        color: #b91c1c;
        font-weight: 700;
        margin-top: 3mm;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    @media print {
        @page {
            size: A5 portrait;
            margin: 0;
        }
        body {
            background: #fff;
        }
        .page {
            width: 148mm;
            height: 210mm;
            margin: 0;
            padding: 12mm;
        }
    }
</style>
</head>
<body>
<div class="page">
    <div class="card">
        <div>
            <div class="header">TAKIMHANE YÖNETİM SİSTEMİ</div>
            <div class="title-area">
                <h1>{{ $station->name }}</h1>
                @if($station->location)
                <div class="loc">📍 {{ $station->location }}</div>
                @endif
            </div>
        </div>

        <div class="qr-container">
            <img
                class="qr-img"
                src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&ecc=H&data={{ urlencode($station->qr_token) }}"
                alt="QR Kod"
            >
        </div>

        <div>
            <div class="token">İstasyon Kodu: {{ $station->qr_token }}</div>

            <div class="steps">
                <div class="steps-title">Zimmet İade Talimatı:</div>
                <ol>
                    <li>Telefonunuzdan personel portalına giriş yapın.</li>
                    <li>Menüden <strong>"Zimmet İade Et"</strong> butonuna basın.</li>
                    <li>Telefon kamerasını açarak bu QR kodu okutun.</li>
                </ol>
            </div>
            
            <div class="warn">Bu QR kodu yetkisiz kişilerle paylaşmayınız!</div>
        </div>
    </div>
</div>

<script>
// QR resmi yüklenince otomatik yazdır
var img = document.querySelector('.qr-img');
function doPrint() { window.print(); }

if (img.complete && img.naturalWidth > 0) {
    setTimeout(doPrint, 300);
} else {
    img.onload  = function() { setTimeout(doPrint, 300); };
    img.onerror = function() {
        img.style.border = '2px dashed #ccc';
        img.alt = 'QR kod yüklenemedi - token: ' + img.src.split('data=')[1];
    };
}
</script>
</body>
</html>
