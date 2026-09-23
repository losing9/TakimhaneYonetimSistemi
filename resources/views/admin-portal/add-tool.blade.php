@extends('admin-portal.layout')
@section('title', 'Hızlı Seri Alet Ekle')

@section('content')
<div style="padding: 16px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div>
            <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">➕ Hızlı Alet Ekle</h2>
            <p class="text-muted">Envantere hızlı ve seri parça ekleme</p>
        </div>
        <a href="{{ route('admin-portal.index') }}" class="btn btn-ghost btn-sm" style="width:auto;">✕ Kapat</a>
    </div>

    <div id="status-alert" class="alert hidden" style="margin: 0 0 12px 0;"></div>

    <div class="card" style="margin:0 0 16px 0; padding:16px;">
        <form id="add-tool-form" onsubmit="submitForm(event)" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label" for="tool-name">Alet / Parça Adı</label>
                <input class="form-input" type="text" id="tool-name" name="name" placeholder="Örn: 10mm Açık Ağız Anahtar" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label class="form-label" for="tool-category">Kategori</label>
                    <select class="form-input form-select" id="tool-category" name="category" required>
                        <option value="">Seçin...</option>
                        @foreach($categories as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tool-max-days">Max Zimmet (Gün)</label>
                    <input class="form-input" type="number" id="tool-max-days" name="max_loan_days" value="7" min="1" max="180" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="tool-slot-search">Bulunduğu Konum (Göz)</label>
                <input class="form-input" type="text" id="tool-slot-search" placeholder="🔍 Göz ara..." style="padding:10px 12px;font-size:13px;margin-bottom:6px;border-style:dashed;">
                <select class="form-input form-select" id="tool-slot" name="slot_id" required>
                    <option value="">Göz Seçin...</option>
                    @foreach($slots as $slot)
                        <option value="{{ $slot['id'] }}">{{ $slot['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Seri Numarası --}}
            <div class="form-group">
                <label class="form-label" for="tool-serial">Seri Numarası / Barkod</label>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <input class="form-input" type="text" id="tool-serial" name="serial_no"
                           placeholder="Boş bırakılırsa otomatik üretilir"
                           style="font-family:monospace; flex:1; min-width:120px;">
                    <button type="button" onclick="openScanner('barcode')"
                            style="padding:0 14px;height:44px;background:#4f46e5;color:#fff;border:none;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;white-space:nowrap;">
                        📷 QR/Barkod
                    </button>
                    <button type="button" onclick="openScanner('ocr')"
                            style="padding:0 14px;height:44px;background:#0d9488;color:#fff;border:none;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;white-space:nowrap;">
                        📝 Yazı Oku (AI)
                    </button>
                </div>
                <small class="text-muted" style="display:block;margin-top:4px;">
                    📷 <b>QR/Barkod</b>: Etiket tarar &nbsp;|&nbsp; 📝 <b>Yazı Oku</b>: Canlı kamera ile Google AI üzerinden seri no okur
                </small>
            </div>

            {{-- Alet Görseli --}}
            <div class="form-group" style="background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:12px;">
                <label class="form-label" style="margin-bottom:6px;">📸 Alet Görseli (Envanter Fotoğrafı)</label>
                <input type="file" id="tool-image-input" accept="image/*" style="display:none;" onchange="handleToolImageSelect(event)">
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="openCameraForToolPhoto()" class="btn btn-primary btn-sm" style="flex:1; background:#4f46e5; font-size:13px;">
                        📷 Kamera ile Çek
                    </button>
                    <button type="button" onclick="document.getElementById('tool-image-input').click()" class="btn btn-ghost btn-sm" style="flex:1; background:rgba(255,255,255,0.08); font-size:13px;">
                        📁 Galeriden Seç
                    </button>
                </div>
                <div id="tool-image-preview-box" style="display:none; margin-top:10px; align-items:center; gap:10px; background:rgba(0,0,0,0.3); padding:8px; border-radius:8px;">
                    <img id="tool-image-preview-img" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                    <div style="flex:1; font-size:11px; color:var(--muted);">
                        <div id="tool-image-file-info" style="font-weight:600; color:#fff;"></div>
                        <div style="color:#10b981; margin-top:2px;">✓ Boyut optimize edildi</div>
                    </div>
                    <button type="button" onclick="removeToolImage()" style="background:rgba(239,68,68,0.2); border:none; color:#ef4444; padding:6px 10px; border-radius:6px; font-size:12px; cursor:pointer;">
                        🗑️ Kaldır
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="tool-description">Açıklama (İsteğe bağlı)</label>
                <textarea class="form-input" id="tool-description" name="description" rows="2"
                          placeholder="Hasar, marka, model vb..." style="resize:none;font-size:13px;"></textarea>
            </div>

            <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:14px;">
                <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:8px;letter-spacing:0.5px;">⚡ Seri Giriş Seçenekleri</div>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:8px;cursor:pointer;">
                    <input type="checkbox" id="keep-fields" checked style="width:16px;height:16px;accent-color:var(--primary);">
                    <span>Alet adı, kategori ve konumu koru</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:8px;cursor:pointer;">
                    <input type="checkbox" id="auto-camera" checked style="width:16px;height:16px;accent-color:var(--primary);">
                    <span>Kaydettikten sonra otomatik kamerayı aç</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" id="auto-save" style="width:16px;height:16px;accent-color:var(--primary);">
                    <span>Barkod okununca otomatik kaydet</span>
                </label>
            </div>

            <button type="submit" id="save-btn" class="btn btn-success" style="width:100%;">
                💾 Aleti Envantere Kaydet
            </button>
        </form>
    </div>

    <div class="card" style="margin:0;padding:16px;">
        <div class="card-title" style="margin-bottom:8px;">Bu Oturumda Eklenen Aletler</div>
        <div id="added-tools-log" style="max-height:180px;overflow-y:auto;font-size:13px;">
            <div id="no-added-tools" class="text-center text-muted" style="padding:12px 0;">Bu oturumda henüz alet eklenmedi.</div>
        </div>
    </div>
</div>

{{-- Canlı Kamera Tarayıcı Modalı --}}
<div id="scanner-modal" class="hidden"
     style="position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:1000;
            display:flex;flex-direction:column;
            padding:calc(env(safe-area-inset-top,0px) + 10px) 14px 14px;">

    <div style="display:flex;justify-content:space-between;align-items:center;color:#fff;padding-bottom:10px;">
        <div id="scanner-title" style="font-weight:700;font-size:16px;">📷 Barkod / QR Tarayıcı</div>
        <button onclick="closeScanner()"
                style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);
                       color:#fff;border-radius:8px;padding:7px 16px;font-size:13px;cursor:pointer;">
            ✕ Kapat
        </button>
    </div>

    {{-- Mod Seçici Sekmeler (QR veya OCR) --}}
    <div id="modal-mode-tabs" style="display:flex;gap:8px;margin-bottom:10px;">
        <button id="tab-barcode" onclick="switchScannerMode('barcode')"
                style="flex:1;padding:10px;border-radius:10px;border:2px solid #4f46e5;background:#4f46e5;color:#fff;font-weight:600;font-size:13px;cursor:pointer;">
            📷 QR / Barkod
        </button>
        <button id="tab-ocr" onclick="switchScannerMode('ocr')"
                style="flex:1;padding:10px;border-radius:10px;border:2px solid #374151;background:transparent;color:#9ca3af;font-weight:600;font-size:13px;cursor:pointer;">
            📝 Yazı Oku (AI)
        </button>
    </div>

    <div id="camera-area" style="flex:1;position:relative;background:#000;border-radius:14px;overflow:hidden;border:2px solid #374151;min-height:220px;">
        {{-- Barkod okuyucu --}}
        <div id="reader" style="width:100%;height:100%;"></div>

        {{-- OCR & Fotoğraf Canlı Video Akışı --}}
        <video id="live-video" autoplay playsinline muted
               style="display:none;width:100%;height:100%;object-fit:cover;position:absolute;inset:0;"></video>

        {{-- Flash / Torch Butonu (canlı kamera modunda) --}}
        <button id="live-torch-btn" onclick="toggleLiveTorch()"
            style="display:none;position:absolute;bottom:12px;right:12px;z-index:30;
                   background:rgba(0,0,0,.55);border:2px solid rgba(255,255,255,.4);
                   border-radius:50%;width:48px;height:48px;font-size:22px;
                   cursor:pointer;backdrop-filter:blur(4px);align-items:center;justify-content:center;"
            title="Flash aç/kapat">💡</button>

        {{-- OCR Kılavuz Çerçevesi (Ortalanmış ve Odaklı) --}}
        <div id="ocr-viewfinder" style="display:none;position:absolute;inset:0;pointer-events:none;">
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <div id="viewfinder-box" style="width:85%;height:110px;border:3px solid #10b981;border-radius:12px;box-shadow:0 0 0 9999px rgba(0,0,0,0.6);"></div>
                <p style="color:#10b981;font-weight:600;margin-top:14px;font-size:13px;text-shadow:0 1px 3px rgba(0,0,0,0.8);">
                    Yazıyı / Seri No'yu yeşil çerçevenin içine denk getirin
                </p>
            </div>
        </div>

        {{-- OCR Sonuç & Alternatif Seçim Paneli --}}
        <div id="ocr-live-result-box" style="display:none;position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.95);padding:12px;border-top:1px solid #374151;max-height:160px;overflow-y:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <span style="font-size:11px;color:#94a3b8;font-weight:600;">⚡ Gemini AI Çıktısı:</span>
                <span id="ocr-ai-engine-tag" style="font-size:10px;background:#4f46e5;color:#fff;padding:2px 6px;border-radius:4px;font-weight:600;">✨ Gemini Vision AI</span>
            </div>
            
            {{-- Ana Seçili Seri No --}}
            <div id="ocr-live-found-text" style="font-family:monospace;font-size:16px;font-weight:700;color:#10b981;word-break:break-all;background:rgba(16,185,129,0.1);padding:6px 8px;border-radius:6px;margin-bottom:6px;border:1px solid rgba(16,185,129,0.3);"></div>
            
            {{-- Alternatif Parçalar Listesi (Tek tıkla seçilebilsin) --}}
            <div id="ocr-candidates-container" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
        </div>

        {{-- İşleniyor Göstergesi --}}
        <div id="ocr-loading-badge" style="display:none;position:absolute;top:12px;right:12px;background:rgba(79,70,229,0.95);color:#fff;border-radius:20px;padding:6px 14px;font-size:12px;font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,0.4);">
            ✨ Gemini AI Okuyor...
        </div>
    </div>

    {{-- OCR Canlı Kontrol Butonları --}}
    <div id="ocr-live-controls" style="display:none;gap:8px;margin-top:10px;">
        <button id="ocr-scan-btn" onclick="captureAndScanGoogleVision()"
                style="flex:2;padding:14px;background:#4f46e5;color:#fff;font-weight:700;font-size:15px;border:none;border-radius:12px;cursor:pointer;">
            ✨ Şimdi Oku (Gemini AI)
        </button>
        <button id="ocr-apply-btn" onclick="applyOcrResult()" disabled
                style="flex:1;padding:14px;background:#10b981;color:#fff;font-weight:700;font-size:14px;border:none;border-radius:12px;cursor:pointer;opacity:0.4;">
            ✓ Kullan
        </button>
    </div>

    {{-- Envanter Fotoğrafı Kontrolü --}}
    <div id="tool-photo-controls" style="display:none;gap:8px;margin-top:10px;">
        <button onclick="takeToolPhotoSnapshot()"
                style="flex:1;padding:14px;background:#4f46e5;color:#fff;font-weight:700;font-size:15px;border:none;border-radius:12px;cursor:pointer;">
            📸 Fotoğrafı Çek
        </button>
    </div>

    <div id="scanner-hint" style="text-align:center;color:#6b7280;font-size:12px;margin-top:8px;">
        Tarayıcıyı alet üzerindeki Barkod veya QR koda doğrultun.
    </div>
</div>

<canvas id="ocr-capture-canvas" style="display:none;"></canvas>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

<script>
// ═══════════════════════════════════════════════════════════════
// State
// ═══════════════════════════════════════════════════════════════
var html5QrCode      = null;
var isSubmitting     = false;
var selectedToolBlob = null;
var ocrResult        = '';
var liveStream       = null;
var currentModalMode = 'barcode'; // 'barcode', 'ocr', 'tool_photo'
var isOcrScanning    = false;

// ═══════════════════════════════════════════════════════════════
// Göz Arama Filtresi
// ═══════════════════════════════════════════════════════════════
var slotSearch   = document.getElementById('tool-slot-search');
var slotSelect   = document.getElementById('tool-slot');
var originalOpts = Array.from(slotSelect.options);

slotSearch.addEventListener('input', function () {
    var term = this.value.toLowerCase().trim();
    slotSelect.innerHTML = '';
    originalOpts.forEach(function (opt) {
        if (!opt.value || opt.text.toLowerCase().includes(term)) {
            slotSelect.appendChild(opt.cloneNode(true));
        }
    });
});

// ═══════════════════════════════════════════════════════════════
// Ses Çalma
// ═══════════════════════════════════════════════════════════════
function playBeep(type) {
    try {
        var ctx  = new (window.AudioContext || window.webkitAudioContext)();
        var osc  = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        if (type === 'success') {
            osc.type = 'sine'; osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            osc.start(); osc.stop(ctx.currentTime + 0.15);
        } else {
            osc.type = 'sawtooth'; osc.frequency.setValueAtTime(220, ctx.currentTime);
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            osc.start(); osc.stop(ctx.currentTime + 0.25);
        }
    } catch (e) {}
}

// ═══════════════════════════════════════════════════════════════
// Resim Küçültme (Blob sıkıştırma)
// ═══════════════════════════════════════════════════════════════
function resizeAndCompressImage(fileOrDataUrl, maxDimension, quality, callback) {
    var img = new Image();
    img.onload = function() {
        var canvas = document.createElement('canvas');
        var w = img.width, h = img.height;
        if (w > h) { if (w > maxDimension) { h = Math.round(h * (maxDimension / w)); w = maxDimension; } }
        else { if (h > maxDimension) { w = Math.round(w * (maxDimension / h)); h = maxDimension; } }
        canvas.width = w; canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);
        canvas.toBlob(function(blob) {
            callback(blob, canvas.toDataURL('image/jpeg', quality), w, h);
        }, 'image/jpeg', quality);
    };
    if (typeof fileOrDataUrl === 'string') { img.src = fileOrDataUrl; }
    else { var r = new FileReader(); r.onload = function(e) { img.src = e.target.result; }; r.readAsDataURL(fileOrDataUrl); }
}

// ═══════════════════════════════════════════════════════════════
// Modal Açma / Kapatma & Canlı Kamera Yönetimi
// ═══════════════════════════════════════════════════════════════
function openScanner(mode) {
    document.getElementById('scanner-modal').classList.remove('hidden');
    switchScannerMode(mode || 'barcode');
}

function closeScanner() {
    stopLiveVideo();
    try {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(function () {});
        }
    } catch (e) {}
    document.getElementById('scanner-modal').classList.add('hidden');
    document.getElementById('ocr-loading-badge').style.display = 'none';
    document.getElementById('ocr-live-result-box').style.display = 'none';
    var applyBtn = document.getElementById('ocr-apply-btn');
    if (applyBtn) { applyBtn.disabled = true; applyBtn.style.opacity = '0.4'; }
    ocrResult = '';
    isOcrScanning = false;
}

function switchScannerMode(mode) {
    currentModalMode = mode;
    var tbB       = document.getElementById('tab-barcode');
    var tbO       = document.getElementById('tab-ocr');
    var tabs      = document.getElementById('modal-mode-tabs');
    var title     = document.getElementById('scanner-title');
    var hint      = document.getElementById('scanner-hint');
    var ctrlOcr   = document.getElementById('ocr-live-controls');
    var ctrlPhoto = document.getElementById('tool-photo-controls');
    var vf        = document.getElementById('ocr-viewfinder');
    var resBox    = document.getElementById('ocr-live-result-box');

    if (mode === 'barcode') {
        tabs.style.display = 'flex';
        tbB.style.background = '#4f46e5'; tbB.style.borderColor = '#4f46e5'; tbB.style.color = '#fff';
        tbO.style.background = 'transparent'; tbO.style.borderColor = '#374151'; tbO.style.color = '#9ca3af';
        title.textContent = '📷 Barkod / QR Tarayıcı';
        hint.textContent  = 'Tarayıcıyı alet üzerindeki Barkod veya QR koda doğrultun.';

        stopLiveVideo();
        document.getElementById('live-video').style.display = 'none';
        vf.style.display = 'none';
        ctrlOcr.style.display = 'none';
        ctrlPhoto.style.display = 'none';
        resBox.style.display = 'none';
        document.getElementById('reader').style.display = 'block';

        startBarcodeScanner();

    } else if (mode === 'ocr') {
        tabs.style.display = 'flex';
        tbO.style.background = '#0d9488'; tbO.style.borderColor = '#0d9488'; tbO.style.color = '#fff';
        tbB.style.background = 'transparent'; tbB.style.borderColor = '#374151'; tbB.style.color = '#9ca3af';
        title.textContent = '📝 Canlı Yazı Okuma (Google AI)';
        hint.textContent  = 'Seri numarasını yeşil kutuya hizalayıp 📸 Şimdi Oku butonuna basın.';

        try {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().catch(function () {});
            }
        } catch(e) {}

        document.getElementById('reader').style.display = 'none';
        ctrlPhoto.style.display = 'none';
        vf.style.display = 'block';
        ctrlOcr.style.display = 'flex';

        startLiveVideo();

    } else if (mode === 'tool_photo') {
        tabs.style.display = 'none';
        title.textContent = '📸 Alet Fotoğrafı Çek';
        hint.textContent  = 'Aletin net bir fotoğrafını çekin.';

        try {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().catch(function () {});
            }
        } catch(e) {}

        document.getElementById('reader').style.display = 'none';
        vf.style.display = 'none';
        ctrlOcr.style.display = 'none';
        resBox.style.display = 'none';
        ctrlPhoto.style.display = 'flex';

        startLiveVideo();
    }
}

// ═══════════════════════════════════════════════════════════════
// Canlı Kamera Akışı (Kasmayan Donanım Hızlandırmalı Video)
// ═══════════════════════════════════════════════════════════════
let liveTorchOn = false;
let liveTorchTrack = null;

function startLiveVideo() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Tarayıcınız kamera akışını desteklemiyor.");
        return;
    }

    var constraints = {
        video: {
            facingMode: { ideal: 'environment' },
            width:  { ideal: 1920 },
            height: { ideal: 1080 }
        }
    };

    function setupTorch(stream) {
        liveStream = stream;
        var video = document.getElementById('live-video');
        video.srcObject = stream;
        video.style.display = 'block';

        // Torch desteği kontrol et
        liveTorchTrack = stream.getVideoTracks()[0];
        liveTorchOn = false;
        var caps = liveTorchTrack.getCapabilities ? liveTorchTrack.getCapabilities() : {};
        var torchBtn = document.getElementById('live-torch-btn');
        if (caps.torch && torchBtn) {
            torchBtn.style.display = 'flex';
            torchBtn.textContent = '💡';
        }
    }

    navigator.mediaDevices.getUserMedia(constraints)
        .then(function (stream) { setupTorch(stream); })
        .catch(function (err) {
            // Düşük çözünürlük fallback
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(function (stream) { setupTorch(stream); })
                .catch(function (e) {
                    alert('Kamera açılamadı: ' + e.message);
                });
        });
}

async function toggleLiveTorch() {
    if (!liveTorchTrack) return;
    liveTorchOn = !liveTorchOn;
    try {
        await liveTorchTrack.applyConstraints({ advanced: [{ torch: liveTorchOn }] });
        var btn = document.getElementById('live-torch-btn');
        btn.textContent  = liveTorchOn ? '🔦' : '💡';
        btn.style.background  = liveTorchOn ? 'rgba(255,220,50,.85)' : 'rgba(0,0,0,.55)';
        btn.style.borderColor = liveTorchOn ? 'rgba(255,200,0,.8)' : 'rgba(255,255,255,.4)';
    } catch(e) { liveTorchOn = !liveTorchOn; }
}


function stopLiveVideo() {
    if (liveStream) {
        try {
            liveStream.getTracks().forEach(function (t) { t.stop(); });
        } catch(e) {}
        liveStream = null;
    }
    var video = document.getElementById('live-video');
    if (video) {
        video.srcObject = null;
        video.style.display = 'none';
    }
    // Flash butonunu gizle ve sıfırla
    liveTorchTrack = null;
    liveTorchOn = false;
    var torchBtn = document.getElementById('live-torch-btn');
    if (torchBtn) {
        torchBtn.style.display = 'none';
        torchBtn.textContent = '💡';
        torchBtn.style.background = 'rgba(0,0,0,.55)';
    }
}

// ═══════════════════════════════════════════════════════════════
// CANLI KAMERADAN GOOGLE VISION AI İLE ANLIK TARAMA (Çerçeve Kırpması + AI)
// ═══════════════════════════════════════════════════════════════
async function captureAndScanGoogleVision() {
    if (isOcrScanning) return;

    var video = document.getElementById('live-video');
    if (!video || !video.videoWidth || video.videoWidth < 10) {
        alert('Kamera görüntüsü bekleniyor, lütfen tekrar deneyin.');
        return;
    }

    var btn       = document.getElementById('ocr-scan-btn');
    var badge     = document.getElementById('ocr-loading-badge');
    var resBox    = document.getElementById('ocr-live-result-box');
    var foundTxt  = document.getElementById('ocr-live-found-text');
    var candBox   = document.getElementById('ocr-candidates-container');
    var applyBtn  = document.getElementById('ocr-apply-btn');

    isOcrScanning = true;
    btn.disabled = true;
    btn.textContent = '⏳ Google AI Analiz Ediyor...';
    badge.style.display = 'block';
    resBox.style.display = 'none';
    candBox.innerHTML = '';
    applyBtn.disabled = true;
    applyBtn.style.opacity = '0.4';

    try {
        var canvas = document.getElementById('ocr-capture-canvas');
        var vw = video.videoWidth;
        var vh = video.videoHeight;

        // Yeşil çerçevenin konumunu kırp (Orta %85 genişlik, %30 yükseklik)
        // Böylece etraftaki alakasız zemin/arka plan metinleri filtrelenir
        var cropX = Math.round(vw * 0.075);
        var cropY = Math.round(vh * 0.35);
        var cropW = Math.round(vw * 0.85);
        var cropH = Math.round(vh * 0.30);

        canvas.width = cropW;
        canvas.height = cropH;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

        // JPEG blob olarak sıkıştır
        canvas.toBlob(async function(blob) {
            try {
                var formData = new FormData();
                formData.append('photo', blob, 'camera_crop.jpg');

                var response = await fetch("{{ route('admin-portal.ocr-scan') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                var data = await response.json();

                if (!data.success || !data.results || data.results.length === 0) {
                    resBox.style.display = 'block';
                    foundTxt.style.color = '#ef4444';
                    foundTxt.textContent = '❌ Yazı bulunamadı. Lütfen daha yakından netleştirin.';
                    playBeep('error');
                    return;
                }

                // Google Vision AI'dan gelen ham metinler
                var rawTexts = data.results.map(function(r) { return r.text; }).join("\n");
                
                // Metinden aday parçaları çıkar (kelimeler, sayılar, barkod kodları vb.)
                var candidates = extractAllCandidates(rawTexts);

                if (candidates.length > 0) {
                    // İlk adayı seçili yap
                    ocrResult = candidates[0];
                    resBox.style.display = 'block';
                    foundTxt.style.color = '#10b981';
                    foundTxt.textContent = 'Seçilen: ' + ocrResult;
                    applyBtn.disabled = false;
                    applyBtn.style.opacity = '1';
                    playBeep('success');
                    if (navigator.vibrate) navigator.vibrate([60, 40, 60]);

                    // Kullanıcının dokunarak seçebileceği butonlar oluştur
                    candidates.forEach(function(cand, idx) {
                        var cBtn = document.createElement('button');
                        cBtn.type = 'button';
                        cBtn.textContent = cand;
                        cBtn.style.cssText = 'padding:6px 10px; background:' + (idx === 0 ? '#10b981' : 'rgba(255,255,255,0.1)') + '; color:#fff; border:1px solid ' + (idx === 0 ? '#10b981' : '#4b5563') + '; border-radius:6px; font-family:monospace; font-size:13px; font-weight:600; cursor:pointer;';
                        
                        cBtn.onclick = function() {
                            ocrResult = cand;
                            foundTxt.textContent = 'Seçilen: ' + cand;
                            // Buton vurgularını güncelle
                            var allBtns = candBox.querySelectorAll('button');
                            allBtns.forEach(function(b) {
                                b.style.background = 'rgba(255,255,255,0.1)';
                                b.style.borderColor = '#4b5563';
                            });
                            cBtn.style.background = '#10b981';
                            cBtn.style.borderColor = '#10b981';
                            playBeep('success');
                        };
                        candBox.appendChild(cBtn);
                    });

                } else {
                    resBox.style.display = 'block';
                    foundTxt.style.color = '#f59e0b';
                    foundTxt.textContent = rawTexts.substring(0, 60);
                    ocrResult = rawTexts.replace(/[^A-Za-z0-9\-_.\/:#]/g, ' ').trim();
                    if (ocrResult.length >= 2) {
                        applyBtn.disabled = false;
                        applyBtn.style.opacity = '1';
                    }
                    playBeep('error');
                }

            } catch (err) {
                resBox.style.display = 'block';
                foundTxt.style.color = '#ef4444';
                foundTxt.textContent = '❌ Bağlantı hatası: ' + err.message;
            } finally {
                isOcrScanning = false;
                btn.disabled = false;
                btn.textContent = '📸 Tekrar Oku (Google AI)';
                badge.style.display = 'none';
            }
        }, 'image/jpeg', 0.95);

    } catch (err) {
        isOcrScanning = false;
        btn.disabled = false;
        btn.textContent = '📸 Şimdi Oku (Google AI)';
        badge.style.display = 'none';
        alert('Kamera hatası: ' + err.message);
    }
}

// Gemini AI çıktısından ve ham metinden anlamlı tüm aday kelimeleri/numaraları ayıkla
function extractAllCandidates(rawText) {
    if (!rawText) return [];
    var list = [];
    var seen = {};

    function addCandidate(cand) {
        if (!cand) return;
        var clean = cand.replace(/SERİ_NO:|DİĞER:|SERI_NO:|SERIAL:|PARÇA_KODU:/gi, '').trim();
        clean = clean.replace(/[^A-Za-z0-9\-_.\/:#]/g, '').trim();
        if (clean.length >= 2 && !seen[clean]) {
            seen[clean] = true;
            list.push(clean);
        }
    }

    // 1. Gemini formatı kontrolü (SERİ_NO: ... / DİĞER: ...)
    var serialMatch = rawText.match(/SERİ_NO:\s*([^\n\r]+)/i);
    if (serialMatch && serialMatch[1]) {
        var serialVal = serialMatch[1].trim();
        if (serialVal && !serialVal.includes('[') && !serialVal.toLowerCase().includes('yok')) {
            addCandidate(serialVal);
        }
    }

    var otherMatch = rawText.match(/DİĞER:\s*([^\n\r]+)/i);
    if (otherMatch && otherMatch[1]) {
        var others = otherMatch[1].split(/[,;\s]+/);
        others.forEach(function(o) { addCandidate(o); });
    }

    // 2. Genel satır ve kelime ayrıştırma
    var lines = rawText.split(/[\n\r]+/);
    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();
        if (line.length >= 2) {
            addCandidate(line);
            var parts = line.split(/[,;\s]+/);
            if (parts.length > 1) {
                parts.forEach(function(p) { addCandidate(p); });
            }
        }
    }

    // 3. Regex ile kod ve seri no desenlerini yakala
    var matches = rawText.match(/[A-Za-z0-9][A-Za-z0-9\-_.\/:#]{1,30}[A-Za-z0-9]/g);
    if (matches) {
        matches.forEach(function(m) { addCandidate(m); });
    }

    return list.slice(0, 8); // En alakalı ilk 8 adayı döndür
}

function applyOcrResult() {
    if (ocrResult) {
        document.getElementById('tool-serial').value = ocrResult;
        playBeep('success');
        closeScanner();
    }
}

// ═══════════════════════════════════════════════════════════════
// Envanter Fotoğrafı
// ═══════════════════════════════════════════════════════════════
function handleToolImageSelect(event) {
    var file = event.target.files[0];
    if (!file) return;
    var previewBox = document.getElementById('tool-image-preview-box');
    var previewImg = document.getElementById('tool-image-preview-img');
    var fileInfo   = document.getElementById('tool-image-file-info');
    previewBox.style.display = 'flex';
    fileInfo.textContent = 'İşleniyor (' + (file.size / (1024*1024)).toFixed(1) + ' MB)...';
    resizeAndCompressImage(file, 1024, 0.8, function(blob, dataUrl, w, h) {
        selectedToolBlob = blob;
        previewImg.src = dataUrl;
        fileInfo.textContent = 'Fotoğraf hazır (' + w + 'x' + h + 'px, ' + Math.round(blob.size/1024) + ' KB)';
        playBeep('success');
    });
}

function openCameraForToolPhoto() {
    document.getElementById('scanner-modal').classList.remove('hidden');
    switchScannerMode('tool_photo');
}

function takeToolPhotoSnapshot() {
    var video = document.getElementById('live-video');
    if (!video || !video.videoWidth) return;
    var canvas = document.createElement('canvas');
    canvas.width = video.videoWidth; canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    var rawDataUrl = canvas.toDataURL('image/jpeg', 0.9);
    var previewBox = document.getElementById('tool-image-preview-box');
    var previewImg = document.getElementById('tool-image-preview-img');
    var fileInfo   = document.getElementById('tool-image-file-info');
    previewBox.style.display = 'flex';
    fileInfo.textContent = 'Optimize ediliyor...';
    resizeAndCompressImage(rawDataUrl, 1024, 0.8, function(blob, dataUrl, w, h) {
        selectedToolBlob = blob;
        previewImg.src = dataUrl;
        fileInfo.textContent = 'Kamera (' + w + 'x' + h + 'px, ' + Math.round(blob.size/1024) + ' KB)';
        playBeep('success');
        closeScanner();
    });
}

function removeToolImage() {
    selectedToolBlob = null;
    document.getElementById('tool-image-input').value = '';
    document.getElementById('tool-image-preview-box').style.display = 'none';
}

// ═══════════════════════════════════════════════════════════════
// Barkod / QR Tarayıcı (Html5Qrcode)
// ═══════════════════════════════════════════════════════════════
function startBarcodeScanner() {
    try {
        if (!html5QrCode) html5QrCode = new Html5Qrcode('reader');
        if (html5QrCode.isScanning) return;

        html5QrCode.start(
            { facingMode: { exact: "environment" } },
            { fps: 15, qrbox: { width: 240, height: 240 } },
            onBarcodeSuccess, function () {}
        ).catch(function() {
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 15, qrbox: { width: 240, height: 240 } },
                onBarcodeSuccess, function () {}
            ).catch(function(err) {
                alert('Kamera açılamadı!\n' + err);
                closeScanner();
            });
        });
    } catch(e) {
        alert("Barkod hatası: " + e.message);
    }
}

function onBarcodeSuccess(decoded) {
    playBeep('success');
    if (navigator.vibrate) navigator.vibrate([60, 40, 60]);
    document.getElementById('tool-serial').value = decoded.trim();
    closeScanner();
    if (document.getElementById('auto-save').checked) {
        setTimeout(function () {
            document.getElementById('add-tool-form').dispatchEvent(new Event('submit'));
        }, 300);
    }
}

// ═══════════════════════════════════════════════════════════════
// Form Gönderimi (AJAX)
// ═══════════════════════════════════════════════════════════════
async function submitForm(event) {
    event.preventDefault();
    if (isSubmitting) return;
    isSubmitting = true;

    var saveBtn = document.getElementById('save-btn');
    saveBtn.disabled    = true;
    saveBtn.textContent = '⏳ Kaydediliyor...';
    saveBtn.style.opacity = '0.5';

    var form     = document.getElementById('add-tool-form');
    var formData = new FormData(form);

    if (selectedToolBlob) {
        formData.set('image', selectedToolBlob, 'tool_photo.jpg');
    }

    try {
        var response = await fetch("{{ route('admin-portal.add-tool.post') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        var data     = await response.json();
        var alertDiv = document.getElementById('status-alert');

        if (!response.ok) {
            playBeep('error');
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = data.error || data.message || 'Bir hata oluştu!';
            alertDiv.classList.remove('hidden');
            return;
        }

        alertDiv.className = 'alert alert-success';
        alertDiv.textContent = data.message;
        alertDiv.classList.remove('hidden');

        var logContainer = document.getElementById('added-tools-log');
        var noAdded      = document.getElementById('no-added-tools');
        if (noAdded) noAdded.remove();

        var logItem = document.createElement('div');
        logItem.style.cssText = 'padding:6px 0;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;';
        logItem.innerHTML = '<span>🔧 <b>' + data.tool.name + '</b></span><span style="font-family:monospace;color:var(--primary);font-weight:700;">' + data.tool.serial_no + '</span>';
        logContainer.insertBefore(logItem, logContainer.firstChild);

        var keepFields = document.getElementById('keep-fields').checked;
        var autoCamera = document.getElementById('auto-camera').checked;

        removeToolImage();

        if (keepFields) {
            document.getElementById('tool-serial').value      = '';
            document.getElementById('tool-description').value = '';
            document.getElementById('tool-serial').focus();
        } else {
            form.reset();
            document.getElementById('tool-max-days').value = '7';
        }

        if (autoCamera) {
            setTimeout(function () { openScanner('barcode'); }, 600);
        }

    } catch (e) {
        playBeep('error');
        alert('Bağlantı hatası!');
    } finally {
        saveBtn.disabled    = false;
        saveBtn.textContent = '💾 Aleti Envantere Kaydet';
        saveBtn.style.opacity = '1';
        isSubmitting = false;
    }
}
</script>
@endpush

