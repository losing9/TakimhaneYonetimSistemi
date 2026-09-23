<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $station->name }} — Haftalık Dinamik QR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #090d16;
            --surface: #111827;
            --border: #1f2937;
            --primary: #f97316;
            --text: #f9fafb;
            --muted: #9ca3af;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }
        .card {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: 28px;
            padding: 40px;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 40px rgba(249, 115, 22, 0.12);
            position: relative;
            overflow: hidden;
        }
        .header-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(249, 115, 22, 0.15);
            color: var(--primary);
            padding: 8px 18px;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            border: 1px solid rgba(249, 115, 22, 0.3);
        }
        .toolroom-name {
            font-size: 14px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .station-name {
            font-size: 26px;
            font-weight: 900;
            color: #fff;
            margin: 6px 0 24px;
            line-height: 1.2;
        }
        .qr-wrapper {
            background: #ffffff;
            padding: 24px;
            border-radius: 24px;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            margin-bottom: 24px;
            border: 4px solid #fff;
        }
        .qr-image {
            width: 260px;
            height: 260px;
            display: block;
        }
        .token-badge {
            background: #1f2937;
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            color: #fbbf24;
            padding: 8px 16px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 16px;
            letter-spacing: 1px;
            border: 1px solid #374151;
        }
        .warning-text {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5;
        }
        .live-clock {
            margin-top: 24px;
            font-size: 13px;
            color: #6b7280;
            font-family: monospace;
        }
        .no-print {
            margin-top: 20px;
        }
        .btn-print {
            background: #374151;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-print:hover { background: #4b5563; }
        @media print {
            body { background: #fff; color: #000; padding: 0; }
            .card { box-shadow: none; border: 2px solid #000; }
            .no-print { display: none; }
            .token-badge { background: #f3f4f6; color: #000; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="header-tag">
        <span>🔒 DİNAMİK HAFTALIK GİRİŞ KAREKODU</span>
    </div>

    <div class="toolroom-name">
        🏢 {{ $station->toolroom?->name ?? 'Takımhane' }}
    </div>

    <div class="station-name">
        {{ $station->name }}
    </div>

    <div class="qr-wrapper">
        <img class="qr-image" src="{{ $qrUri }}" alt="Haftalık Karekod">
    </div>

    <div>
        <span class="token-badge">{{ $token }}</span>
    </div>

    <p class="warning-text">
        Bu karekod <strong>{{ now()->format('o') }} / {{ now()->format('W') }}. Hafta</strong> için geçerlidir.<br>
        Her Pazartesi günü güvenlik amacıyla otomatik olarak yenilenir.
    </p>

    <div class="live-clock" id="live-clock">
        Canlı Zaman: {{ now()->format('d.m.Y H:i:s') }}
    </div>
</div>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Sayfayı Yazdır (A4)</button>
</div>

<script>
    // Canlı saat ve 10 dakikada bir otomatik yenileme (Pazartesi geçişlerini yakalamak için)
    setInterval(() => {
        const d = new Date();
        document.getElementById('live-clock').textContent = 'Canlı Zaman: ' + d.toLocaleTimeString('tr-TR');
    }, 1000);

    setTimeout(() => {
        window.location.reload();
    }, 600000); // 10 dakika
</script>

</body>
</html>
