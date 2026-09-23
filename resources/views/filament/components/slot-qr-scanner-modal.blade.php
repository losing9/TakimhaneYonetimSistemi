<div x-data="{
    stream: null,
    html5QrCode: null,
    manualInput: '',
    error: '',
    scanning: false,

    init() {
        this.$nextTick(() => this.startScan());
    },

    startScan() {
        this.error = '';
        this.scanning = true;
        if (!this.html5QrCode && document.getElementById('slot-qr-reader')) {
            this.html5QrCode = new Html5Qrcode('slot-qr-reader');
        }
        if (this.html5QrCode) {
            this.html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 15, qrbox: { width: 220, height: 220 } },
                (decoded) => this.handleCode(decoded),
                () => {}
            ).catch(err => {
                this.error = 'Kamera başlatılamadı: ' + err;
                this.scanning = false;
            });
        }
    },

    stopScan() {
        if (this.html5QrCode && this.html5QrCode.isScanning) {
            this.html5QrCode.stop().catch(() => {});
        }
        this.scanning = false;
    },

    handleCode(code) {
        code = (code || '').trim();
        if (!code) return;
        if (navigator.vibrate) navigator.vibrate([60, 40, 60]);
        this.stopScan();

        // SLOT:id|label formatı veya doğrudan sayı
        let slotId = null;
        let match = code.match(/^SLOT:(\d+)/i);
        if (match) {
            slotId = match[1];
        } else if (/^\d+$/.test(code)) {
            slotId = code;
        }

        if (slotId) {
            // İlgili gözün takımlar listesini aç (Aletler tablosunda bu gözü filtrele veya portala yönlendir)
            window.location.href = '{{ route('filament.admin.resources.tools.index') }}?tableFilters[slot_id][value]=' + slotId;
        } else {
            // Parça QR kodu okutulmuş olabilir
            window.location.href = '{{ route('filament.admin.resources.tools.index') }}?tableSearch=' + encodeURIComponent(code);
        }
    },

    submitManual() {
        if (this.manualInput.trim()) {
            this.handleCode(this.manualInput.trim());
        }
    }
}" x-init="init()" class="space-y-4">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <div class="text-xs text-gray-500 dark:text-gray-400">
        Raf veya göz üzerindeki QR etiketi kameraya gösterin. Okunduğunda o gözdeki tüm takımlar filtrelenmiş olarak açılacaktır.
    </div>

    {{-- Kamera Görüntüsü --}}
    <div class="relative w-full aspect-square max-w-sm mx-auto bg-black rounded-2xl overflow-hidden shadow-inner">
        <div id="slot-qr-reader" class="w-full h-full"></div>
    </div>

    {{-- Hata Mesajı --}}
    <div x-show="error" class="p-3 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 text-xs flex items-center gap-2">
        <span>⚠️</span>
        <span x-text="error"></span>
    </div>

    {{-- Manuel Giriş --}}
    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex gap-2">
        <input
            type="text"
            x-model="manualInput"
            @keydown.enter.prevent="submitManual()"
            placeholder="Veya QR metnini / Göz ID yazın..."
            class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono"
        >
        <button
            type="button"
            @click="submitManual()"
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl transition"
        >
            Aç
        </button>
    </div>
</div>
