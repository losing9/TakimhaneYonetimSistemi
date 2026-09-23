@extends('portal.layout')
@section('title', 'QR Tara - Zimmet Al')

@section('content')
<div style="padding: 16px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">📷 QR ile Zimmet Al</h2>
    <p class="text-muted" style="margin-bottom:16px;">Aletin etiketindeki QR kodu okutun</p>

    {{-- QR Tarayıcı --}}
    <div id="qr-video-container" style="position:relative;">
        <video id="qr-video" autoplay playsinline muted></video>
        <div class="qr-overlay">
            <div style="position:relative;display:flex;align-items:center;justify-content:center;">
                <div class="qr-frame"></div>
                <div class="qr-scan-line"></div>
            </div>
        </div>
        {{-- Flash / Torch Butonu --}}
        <button id="torch-btn" onclick="toggleTorch()"
            style="display:none;position:absolute;bottom:12px;right:12px;z-index:20;
                   background:rgba(0,0,0,.55);border:2px solid rgba(255,255,255,.4);
                   border-radius:50%;width:48px;height:48px;font-size:22px;
                   cursor:pointer;backdrop-filter:blur(4px);transition:all .2s;"
            title="Flash aç/kapat">
            💡
        </button>
    </div>

    {{-- Kamera izni hatası --}}
    <div id="camera-error" class="alert alert-danger mt-2 hidden">
        📵 Kamera erişimi reddedildi. Lütfen tarayıcı ayarlarından kamera iznini etkinleştirin.
    </div>

    {{-- Manuel giriş --}}
    <div class="card" style="margin-top:12px;">
        <div class="card-title">Manuel Seri No Giriş</div>
        <div style="display:flex;gap:8px;">
            <input class="form-input" type="text" id="manual-serial" placeholder="TKM-2024-..." style="flex:1;">
            <button onclick="searchTool(document.getElementById('manual-serial').value)" class="btn btn-primary btn-sm" style="width:auto;white-space:nowrap;">
                Ara
            </button>
        </div>
    </div>

    {{-- Alet Bilgisi (bulunduğunda) --}}
    <div id="tool-result" class="hidden">
        <div class="card" style="border-color: var(--success);">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:50px;height:50px;border-radius:12px;background:rgba(34,197,94,.15);display:flex;align-items:center;justify-content:center;font-size:26px;">🔧</div>
                <div style="flex:1;">
                    <div id="result-name" style="font-size:16px;font-weight:700;"></div>
                    <div id="result-serial" style="font-size:13px;color:var(--muted);"></div>
                    <div id="result-location" style="font-size:13px;color:var(--muted);"></div>
                </div>
                <span class="badge badge-active">Müsait ✅</span>
            </div>

            <form action="{{ route('portal.take-loan') }}" method="POST">
                @csrf
                <input type="hidden" id="tool_id_input" name="tool_id" value="">

                <div class="form-group">
                    <label class="form-label">Kaç gün zimmetlensin?</label>
                    <select name="loan_days" class="form-input form-select">
                        <option value="1">1 gün</option>
                        <option value="3">3 gün</option>
                        <option value="7" selected>1 hafta</option>
                        <option value="14">2 hafta</option>
                        <option value="30">1 ay</option>
                        <option value="90">3 ay</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    ✅ Zimmet Al
                </button>
            </form>
        </div>
    </div>

    {{-- Hata --}}
    <div id="tool-error" class="alert alert-danger mt-2 hidden"></div>
</div>
@endsection

@push('scripts')
<script>
const FIND_URL   = "{{ route('portal.find-tool') }}";
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

let stream = null;
let scanning = false;
let lastScanned = '';
let scanCooldown = false;

// ─── jsQR CDN ─────────────────────────────────────────────────────────────────
const script = document.createElement('script');
script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
script.onload = startCamera;
document.head.appendChild(script);

let torchOn = false;
let videoTrack = null;

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        const video = document.getElementById('qr-video');
        video.srcObject = stream;
        video.play();
        scanning = true;
        requestAnimationFrame(tick);

        // Torch (flash) desteği kontrolü
        videoTrack = stream.getVideoTracks()[0];
        const caps = videoTrack.getCapabilities ? videoTrack.getCapabilities() : {};
        if (caps.torch) {
            document.getElementById('torch-btn').style.display = 'flex';
            document.getElementById('torch-btn').style.alignItems = 'center';
            document.getElementById('torch-btn').style.justifyContent = 'center';
        }
    } catch(e) {
        document.getElementById('camera-error').classList.remove('hidden');
    }
}

async function toggleTorch() {
    if (!videoTrack) return;
    torchOn = !torchOn;
    try {
        await videoTrack.applyConstraints({ advanced: [{ torch: torchOn }] });
        const btn = document.getElementById('torch-btn');
        btn.textContent = torchOn ? '🔦' : '💡';
        btn.style.background = torchOn ? 'rgba(255,220,50,.85)' : 'rgba(0,0,0,.55)';
        btn.style.borderColor = torchOn ? 'rgba(255,200,0,.8)' : 'rgba(255,255,255,.4)';
    } catch(e) {
        torchOn = !torchOn; // geri al
    }
}

function tick() {
    if (!scanning) return;
    const video = document.getElementById('qr-video');
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        const canvas = document.createElement('canvas');
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
        if (code && code.data && !scanCooldown) {
            const raw = code.data.trim();
            if (raw !== lastScanned) {
                lastScanned = raw;
                scanCooldown = true;
                setTimeout(() => { scanCooldown = false; }, 3000);
                // SLOT QR mı yoksa alet QR mı?
                if (raw.startsWith('SLOT:')) {
                    handleSlotQr(raw);
                } else {
                    searchTool(raw);
                }
            }
        }
    }
    requestAnimationFrame(tick);
}

async function searchTool(serial) {
    if (!serial) return;
    hideResults();

    const res = await fetch(FIND_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        body: JSON.stringify({ serial_no: serial }),
    });
    const data = await res.json();

    if (!res.ok) {
        document.getElementById('tool-error').textContent = data.error;
        document.getElementById('tool-error').classList.remove('hidden');
        return;
    }

    // Başarılı — alet bilgilerini doldur
    document.getElementById('result-name').textContent     = data.name;
    document.getElementById('result-serial').textContent   = '# ' + data.serial_no;
    document.getElementById('result-location').textContent = '📍 ' + (data.location || 'Konum yok');
    document.getElementById('tool_id_input').value         = data.id;
    document.getElementById('tool-result').classList.remove('hidden');

    // Titreşim (mobil)
    if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
}

function hideResults() {
    document.getElementById('tool-result').classList.add('hidden');
    document.getElementById('tool-error').classList.add('hidden');
}

// Slot QR okunduğunda çalışır — doğrudan slot sayfasına yönlendirir
async function handleSlotQr(raw) {
    hideResults();
    if (navigator.vibrate) navigator.vibrate([60, 30, 60]);
    const FIND_SLOT_URL = "{{ route('portal.find-slot') }}";
    try {
        const res = await fetch(FIND_SLOT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ qr: raw }),
        });
        const data = await res.json();
        if (res.ok && data.url) {
            window.location.href = data.url;
        } else {
            document.getElementById('tool-error').textContent = data.error || 'Göz bulunamadı.';
            document.getElementById('tool-error').classList.remove('hidden');
        }
    } catch(e) {
        document.getElementById('tool-error').textContent = 'Bağlantı hatası.';
        document.getElementById('tool-error').classList.remove('hidden');
    }
}
</script>
@endpush
