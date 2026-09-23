{{-- Filament Global Lightbox Modal --}}
<div
    x-data="{
        open: false,
        imgSrc: '',
        imgCaption: '',
        show(src, caption) {
            this.imgSrc = src;
            this.imgCaption = caption || '';
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        hide() {
            this.open = false;
            document.body.style.overflow = '';
        }
    }"
    x-init="
        window.filamentOpenLightbox = (src, caption) => show(src, caption);
        document.addEventListener('click', (e) => {
            const target = e.target.closest('img');
            if (target && !target.closest('.no-lightbox') && (target.closest('.fi-ta-image') || target.closest('.fi-in-image') || target.classList.contains('cursor-pointer'))) {
                const fullSrc = target.getAttribute('data-full-src') || target.src;
                if (fullSrc && !fullSrc.includes('ui-avatars.com') && !fullSrc.includes('svg')) {
                    e.preventDefault();
                    e.stopPropagation();
                    show(fullSrc, target.getAttribute('alt') || '');
                }
            }
        }, true);
    "
    @keydown.escape.window="hide()"
    class="relative z-[99999]"
>
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/90 backdrop-blur-md flex items-center justify-center p-4"
        style="display: none;"
        @click="hide()"
    >
        {{-- Kapat Butonu --}}
        <button
            type="button"
            @click.stop="hide()"
            class="absolute top-4 right-4 z-10 w-11 h-11 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center font-bold text-xl shadow-xl transition-transform hover:scale-105"
            title="Kapat (ESC)"
        >
            ✕
        </button>

        {{-- İçerik Kutusu --}}
        <div
            @click.stop
            class="relative max-w-[95vw] max-h-[92vh] flex flex-col items-center"
        >
            <img
                :src="imgSrc"
                :alt="imgCaption"
                class="max-w-[95vw] max-h-[82vh] object-contain rounded-2xl shadow-2xl border border-white/10"
            >

            {{-- Başlık & Aksiyonlar --}}
            <div
                x-show="imgCaption"
                class="mt-3 px-4 py-2 rounded-xl bg-gray-900/90 border border-white/10 text-white text-sm font-semibold max-w-lg text-center flex items-center gap-3 shadow-lg"
            >
                <span x-text="imgCaption"></span>
                <a
                    :href="imgSrc"
                    target="_blank"
                    class="text-xs text-primary-400 hover:text-primary-300 underline font-normal"
                >
                    Tam Boyut ↗
                </a>
            </div>
        </div>
    </div>
</div>
