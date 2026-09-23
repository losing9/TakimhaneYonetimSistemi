@extends('portal.layout')
@section('title', 'Alet İade Et')

@section('content')
<div style="padding:16px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">↩️ Alet İade Et</h2>
    <p class="text-muted" style="margin-bottom:16px;">3 adımda güvenli iade</p>

    {{-- Progress Steps --}}
    <div style="display:flex;align-items:center;gap:0;margin-bottom:20px;">
        @foreach([['1','İstasyon QR'],['2','Alet QR'],['3','Fotoğraf & Onayla']] as $i => $step)
        <div style="flex:1;text-align:center;">
            <div id="step-dot-{{ $i+1 }}" style="width:32px;height:32px;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;transition:all .3s;
                {{ $i===0 ? 'background:var(--primary);color:#fff;' : 'background:var(--surface2);color:var(--muted);border:1px solid var(--border);' }}">
                {{ $step[0] }}
            </div>
            <div style="font-size:10px;color:var(--muted);">{{ $step[1] }}</div>
        </div>
        @if($i < 2)
        <div style="flex:1;height:2px;background:var(--border);margin-bottom:16px;"></div>
        @endif
        @endforeach
    </div>

    {{-- ADIM 1: İstasyon QR ────────────────────────────────────────────────── --}}
    <div id="step-1">
        <div class="card" style="border-color:var(--primary);">
            <div class="card-title">📍 Adım 1: Takımhane İstasyonu QR'ını Okut</div>
            <p class="text-muted" style="font-size:13px;margin-bottom:14px;">
                Takımhane girişindeki duvara asılı QR kodu kameranıza gösterin.
            </p>
            <div id="qr-video-container-1" style="position:relative;">
                <video id="qr-video-1" autoplay playsinline muted></video>
                <div class="qr-overlay">
                    <div style="position:relative;display:flex;align-items:center;justify-content:center;">
                        <div class="qr-frame"></div>
                        <div class="qr-scan-line"></div>
                    </div>
                </div>
                <button id="torch-btn-1" onclick="toggleTorch('torch-btn-1')"
                    style="display:none;position:absolute;bottom:12px;right:12px;z-index:20;
                           background:rgba(0,0,0,.55);border:2px solid rgba(255,255,255,.4);
                           border-radius:50%;width:48px;height:48px;font-size:22px;
                           cursor:pointer;backdrop-filter:blur(4px);align-items:center;justify-content:center;"
                    title="Flash aç/kapat">💡</button>
            </div>
            <div class="card-title" style="margin-top:12px;margin-bottom:6px;">Manuel Giriş</div>
            <div style="display:flex;gap:8px;">
                <input class="form-input" id="manual-station" placeholder="STATION-TAKIM-..." style="flex:1;font-size:13px;">
                <button onclick="verifyStation(document.getElementById('manual-station').value)" class="btn btn-primary btn-sm" style="width:auto;">Doğrula</button>
            </div>
        </div>
        <div id="station-error" class="alert alert-danger hidden"></div>
        <div id="station-success" class="alert alert-success hidden"></div>
    </div>

    {{-- ADIM 2: Alet QR ─────────────────────────────────────────────────────── --}}
    <div id="step-2" class="hidden">
        <div class="card" style="border-color:var(--success);">
            <div class="card-title">🔧 Adım 2: İade Edeceğiniz Aletin QR'ını Okut</div>
            <div id="qr-video-container-2" style="position:relative;">
                <video id="qr-video-2" autoplay playsinline muted></video>
                <div class="qr-overlay">
                    <div style="position:relative;display:flex;align-items:center;justify-content:center;">
                        <div class="qr-frame"></div>
                        <div class="qr-scan-line"></div>
                    </div>
                </div>
                <button id="torch-btn-2" onclick="toggleTorch('torch-btn-2')"
                    style="display:none;position:absolute;bottom:12px;right:12px;z-index:20;
                           background:rgba(0,0,0,.55);border:2px solid rgba(255,255,255,.4);
                           border-radius:50%;width:48px;height:48px;font-size:22px;
                           cursor:pointer;backdrop-filter:blur(4px);align-items:center;justify-content:center;"
                    title="Flash aç/kapat">💡</button>
            </div>
            <div class="card-title" style="margin-top:12px;margin-bottom:6px;">Manuel Giriş</div>
            <div style="display:flex;gap:8px;">
                <input class="form-input" id="manual-tool" placeholder="TKM-2024-..." style="flex:1;font-size:13px;">
                <button onclick="findLoan(document.getElementById('manual-tool').value)" class="btn btn-primary btn-sm" style="width:auto;">Ara</button>
            </div>
        </div>
        <div id="loan-error" class="alert alert-danger hidden"></div>
        <div id="loan-success" class="alert alert-success hidden"></div>
    </div>

    {{-- ADIM 3: Fotoğraf & Onay ─────────────────────────────────────────────── --}}
    <div id="step-3" class="hidden">
        <div class="card">
            <div class="card-title">📸 Adım 3: Aletin Fotoğrafını Çek</div>
            <p class="text-muted" style="font-size:13px;margin-bottom:14px;">
                Aletin mevcut durumunu fotoğraflayın. Hasar, kir, eksik parça varsa fotoğrafta görünmeli.
            </p>

            {{-- Fotoğraf Çekme --}}
            <div id="photo-preview-container" style="width:100%;aspect-ratio:4/3;background:var(--surface2);border-radius:12px;border:2px dashed var(--border);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;cursor:pointer;overflow:hidden;" onclick="document.getElementById('photo-input').click()">
                <img id="photo-preview" style="width:100%;height:100%;object-fit:cover;display:none;">
                <div id="photo-placeholder" style="text-align:center;">
                    <div style="font-size:40px;">📸</div>
                    <div style="font-size:13px;color:var(--muted);">Fotoğraf çekmek için dokunun</div>
                </div>
            </div>
            <input type="file" id="photo-input" accept="image/*" capture="environment" style="display:none;" onchange="previewPhoto(this)">

            <form id="return-form" action="{{ route('portal.confirm-return') }}" method="POST" enctype="multipart/form-data" style="margin-top:14px;">
                @csrf
                <input type="hidden" id="return-loan-id"    name="loan_id"    value="">
                <input type="hidden" id="return-station-id" name="station_id" value="">
                {{-- Gerçek dosya input --}}
                <div id="real-photo-wrapper"></div>

                <div class="form-group">
                    <label class="form-label">İade Notu (opsiyonel)</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Alet durumu hakkında not..." style="resize:none;"></textarea>
                </div>

                <button type="button" id="confirm-btn" onclick="submitReturn()" class="btn btn-success" disabled style="opacity:.5;">
                    ✅ İadeyi Onayla
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const STATION_URL  = "{{ route('portal.verify-station') }}";
const LOAN_URL     = "{{ route('portal.find-my-loan') }}";

let stationId = null, loanId = null, photoFile = null;
let activeScanner = null;

// ─── Adım 1: İstasyon QR ─────────────────────────────────────────────────────
const jsqrScript = document.createElement('script');
jsqrScript.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
jsqrScript.onload = () => startScanner('qr-video-1', verifyStation);
document.head.appendChild(jsqrScript);

// ─── Torch (Flash) Yönetimi ────────────────────────────────────────────────────
const torchTracks = {}; // { 'torch-btn-1': track, 'torch-btn-2': track }
const torchState  = {};

async function toggleTorch(btnId) {
    const track = torchTracks[btnId];
    if (!track) return;
    torchState[btnId] = !torchState[btnId];
    try {
        await track.applyConstraints({ advanced: [{ torch: torchState[btnId] }] });
        const btn = document.getElementById(btnId);
        btn.textContent  = torchState[btnId] ? '🔦' : '💡';
        btn.style.background  = torchState[btnId] ? 'rgba(255,220,50,.85)' : 'rgba(0,0,0,.55)';
        btn.style.borderColor = torchState[btnId] ? 'rgba(255,200,0,.8)' : 'rgba(255,255,255,.4)';
    } catch(e) { torchState[btnId] = !torchState[btnId]; }
}

function startScanner(videoId, callback) {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(stream => {
            const video = document.getElementById(videoId);
            video.srcObject = stream;
            video.play();

            // Torch desteği kontrolü
            const vtrack = stream.getVideoTracks()[0];
            const numSuffix = videoId.replace('qr-video-', '');
            const btnId = 'torch-btn-' + numSuffix;
            torchTracks[btnId] = vtrack;
            torchState[btnId]  = false;
            const caps = vtrack.getCapabilities ? vtrack.getCapabilities() : {};
            if (caps.torch) {
                const btn = document.getElementById(btnId);
                if (btn) { btn.style.display = 'flex'; }
            }

            let cooldown = false, last = '';
            function tick() {
                if (!document.getElementById(videoId)) return;
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    const c = document.createElement('canvas');
                    c.width = video.videoWidth; c.height = video.videoHeight;
                    c.getContext('2d').drawImage(video, 0, 0);
                    const img = c.getContext('2d').getImageData(0,0,c.width,c.height);
                    const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
                    if (code && code.data && !cooldown && code.data !== last) {
                        last = code.data; cooldown = true;
                        setTimeout(() => cooldown = false, 3000);
                        stream.getTracks().forEach(t => t.stop());
                        callback(code.data.trim());
                        return;
                    }
                }
                requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }).catch(() => {});
}


async function verifyStation(token) {
    const res = await fetch(STATION_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ token }),
    });
    const data = await res.json();
    if (!res.ok) {
        document.getElementById('station-error').textContent = data.error;
        document.getElementById('station-error').classList.remove('hidden');
        return;
    }
    stationId = data.station_id;
    document.getElementById('station-success').textContent = '✅ ' + data.station_name + ' doğrulandı!';
    document.getElementById('station-success').classList.remove('hidden');
    document.getElementById('station-error').classList.add('hidden');
    if (navigator.vibrate) navigator.vibrate([50,30,50]);

    setTimeout(() => {
        document.getElementById('step-1').classList.add('hidden');
        document.getElementById('step-2').classList.remove('hidden');
        document.getElementById('step-dot-1').style.cssText = 'width:32px;height:32px;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:#22c55e;color:#fff;';
        document.getElementById('step-dot-2').style.cssText = 'width:32px;height:32px;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:var(--primary);color:#fff;';
        startScanner('qr-video-2', findLoan);
    }, 800);
}

async function findLoan(serial) {
    const res = await fetch(LOAN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ serial_no: serial }),
    });
    const data = await res.json();
    if (!res.ok) {
        document.getElementById('loan-error').textContent = data.error;
        document.getElementById('loan-error').classList.remove('hidden');
        return;
    }
    loanId = data.loan_id;
    document.getElementById('loan-success').textContent = '✅ ' + data.tool_name + ' bulundu!';
    document.getElementById('loan-success').classList.remove('hidden');
    document.getElementById('loan-error').classList.add('hidden');
    if (navigator.vibrate) navigator.vibrate([50,30,50]);

    setTimeout(() => {
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-3').classList.remove('hidden');
        document.getElementById('return-loan-id').value    = loanId;
        document.getElementById('return-station-id').value = stationId;
        document.getElementById('step-dot-2').style.cssText = 'width:32px;height:32px;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:#22c55e;color:#fff;';
        document.getElementById('step-dot-3').style.cssText = 'width:32px;height:32px;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:var(--primary);color:#fff;';
    }, 800);
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Show loading state or disable submit until compression finishes
        document.getElementById('confirm-btn').disabled = true;
        document.getElementById('confirm-btn').style.opacity = '0.5';
        document.getElementById('confirm-btn').textContent = '⏳ Fotoğraf İşleniyor...';

        const reader = new FileReader();
        reader.onload = function (e) {
            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                // Max dimensions: 1000px
                const max_size = 1000;
                if (width > height) {
                    if (width > max_size) {
                        height *= max_size / width;
                        width = max_size;
                    }
                } else {
                    if (height > max_size) {
                        width *= max_size / height;
                        height = max_size;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                // Compress as JPEG with 0.7 quality (very small size, high quality)
                canvas.toBlob(function (blob) {
                    photoFile = new File([blob], "photo.jpg", { type: "image/jpeg", lastModified: Date.now() });
                    
                    // Update preview
                    document.getElementById('photo-preview').src = URL.createObjectURL(photoFile);
                    document.getElementById('photo-preview').style.display = 'block';
                    document.getElementById('photo-placeholder').style.display = 'none';
                    
                    // Enable submit
                    document.getElementById('confirm-btn').disabled = false;
                    document.getElementById('confirm-btn').style.opacity = '1';
                    document.getElementById('confirm-btn').textContent = '✅ İadeyi Onayla';
                    
                    // Append file to form input
                    const wrapper = document.getElementById('real-photo-wrapper');
                    wrapper.innerHTML = '';
                    const realInput = document.createElement('input');
                    realInput.type = 'file'; realInput.name = 'photo'; realInput.style.display = 'none';
                    const dt = new DataTransfer(); dt.items.add(photoFile); realInput.files = dt.files;
                    wrapper.appendChild(realInput);
                }, 'image/jpeg', 0.7);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function submitReturn() {
    if (!photoFile) { alert('Lütfen önce fotoğraf çekin.'); return; }
    document.getElementById('return-form').submit();
}
</script>
@endpush
