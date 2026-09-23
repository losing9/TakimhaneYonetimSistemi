<div x-data="{
    open: false,
    mode: 'barcode',
    stream: null,
    html5QrCode: null,
    ocrResult: '',
    scanCount: 0,
    isScanning: false,

    openModal(m) {
        this.mode = m || 'barcode';
        this.open = true;
        this.$nextTick(() => { this.startScanner(); });
    },

    closeModal() {
        this.stopScanner();
        this.open = false;
    },

    switchTab(m) {
        this.stopScanner();
        this.mode = m;
        this.$nextTick(() => { this.startScanner(); });
    },

    startScanner() {
        if (this.mode === 'barcode') {
            if (!this.html5QrCode) {
                this.html5QrCode = new Html5Qrcode('filament-reader');
            }
            this.html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 15, qrbox: { width: 240, height: 240 } },
                (decoded) => {
                    this.onSuccess(decoded);
                },
                () => {}
            ).catch(err => {
                alert('Kamera başlatılamadı: ' + err);
                this.closeModal();
            });
        } else if (this.mode === 'ocr') {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(st => {
                    this.stream = st;
                    let video = document.getElementById('filament-ocr-video');
                    video.srcObject = st;
                    video.style.display = 'block';
                })
                .catch(err => alert('Kamera açılamadı: ' + err.message));
        }
    },

    stopScanner() {
        if (this.html5QrCode && this.html5QrCode.isScanning) {
            this.html5QrCode.stop().catch(() => {});
        }
        if (this.stream) {
            this.stream.getTracks().forEach(t => t.stop());
            this.stream = null;
        }
        let video = document.getElementById('filament-ocr-video');
        if (video) {
            video.srcObject = null;
            video.style.display = 'none';
        }
    },

    captureOcr() {
        let video = document.getElementById('filament-ocr-video');
        let canvas = document.getElementById('filament-ocr-canvas');
        if (!video || !video.videoWidth) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        let dataUrl = canvas.toDataURL('image/png');

        if (typeof Tesseract !== 'undefined') {
            Tesseract.recognize(dataUrl, 'eng', {
                tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_./:# '
            }).then(res => {
                let cleaned = (res.data.text || '').replace(/[^A-Za-z0-9\-_\.\/:#]/g, '').trim();
                if (cleaned) {
                    this.onSuccess(cleaned);
                } else {
                    alert('Seri numarası/metin okunamadı.');
                }
            }).catch(e => alert('OCR Hatası: ' + e.message));
        }
    },

    onSuccess(val) {
        let serialInput = document.querySelector('input[wire\\:model*=\"serial_no\"], input[id*=\"serial_no\"]');
        if (serialInput) {
            serialInput.value = val;
            serialInput.dispatchEvent(new Event('input', { bubbles: true }));
            serialInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        let barcodeInput = document.querySelector('input[wire\\:model*=\"barcode\"], input[id*=\"barcode\"]');
        if (barcodeInput && !barcodeInput.value) {
            barcodeInput.value = val;
            barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
            barcodeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        this.closeModal();
    }
}" class="flex gap-2 mt-2">

    <button type="button" @click="openModal('barcode')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
        📷 QR/Barkod Oku
    </button>

    <button type="button" @click="openModal('ocr')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-500 rounded-lg shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        📝 Yazı Oku (OCR)
    </button>

    {{-- Scanner Modal --}}
    <template x-teleport="body">
        <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex flex-col p-4 bg-black/90 backdrop-blur-md">
            <div class="flex items-center justify-between pb-3 text-white">
                <h3 class="text-base font-bold" x-text="mode === 'barcode' ? '📷 QR / Barkod Tarayıcı' : '📝 Yazı Okuma (OCR)'"></h3>
                <button type="button" @click="closeModal()" class="px-3 py-1.5 text-xs font-semibold text-white bg-white/10 hover:bg-white/20 rounded-lg border border-white/20">
                    ✕ Kapat
                </button>
            </div>

            <div class="flex gap-2 mb-3">
                <button type="button" @click="switchTab('barcode')" :class="mode === 'barcode' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-transparent text-gray-400 border-gray-700'" class="flex-1 py-2 text-xs font-semibold rounded-lg border">
                    📷 QR / Barkod
                </button>
                <button type="button" @click="switchTab('ocr')" :class="mode === 'ocr' ? 'bg-teal-600 text-white border-teal-600' : 'bg-transparent text-gray-400 border-gray-700'" class="flex-1 py-2 text-xs font-semibold rounded-lg border">
                    📝 Yazı Oku (OCR)
                </button>
            </div>

            <div class="relative flex-1 bg-black rounded-xl overflow-hidden border border-gray-800 min-h-[250px]">
                <div id="filament-reader" x-show="mode === 'barcode'" class="w-full h-full"></div>
                <video id="filament-ocr-video" x-show="mode === 'ocr'" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <canvas id="filament-ocr-canvas" class="hidden"></canvas>
            </div>

            <div x-show="mode === 'ocr'" class="flex gap-2 mt-3">
                <button type="button" @click="captureOcr()" class="flex-1 py-3 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl">
                    📸 Oku
                </button>
            </div>
        </div>
    </template>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/5.1.0/tesseract.min.js"></script>
