<div class="p-2 flex flex-col items-center justify-center text-center">
    @if($record && $record->image)
        <div class="relative group max-w-full overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-950/5 dark:bg-gray-950/40 p-2 shadow-inner">
            <img
                src="{{ \Illuminate\Support\Facades\Storage::url($record->image) }}"
                alt="{{ $record->name }}"
                class="max-h-[70vh] max-w-full rounded-xl object-contain shadow-md mx-auto transition-transform duration-300 group-hover:scale-[1.02]"
                onerror="this.style.display='none'; document.getElementById('image-fallback-{{ $record->id }}').style.display='flex';"
            >
            <div id="image-fallback-{{ $record->id }}" style="display:none;" class="w-64 h-64 flex flex-col items-center justify-center text-gray-400">
                <span class="text-5xl mb-2">🔧</span>
                <span class="text-xs">Görsel yüklenemedi</span>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 font-semibold border border-primary-200 dark:border-primary-800">
                🏷️ {{ $record->name }}
            </span>
            @if($record->serial_no)
                <span class="px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-mono border border-gray-200 dark:border-gray-700">
                    # {{ $record->serial_no }}
                </span>
            @endif
            @if($record->location_label)
                <span class="px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                    📍 {{ $record->location_label }}
                </span>
            @endif
            <a
                href="{{ \Illuminate\Support\Facades\Storage::url($record->image) }}"
                target="_blank"
                class="px-2.5 py-1 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-primary-600 hover:text-white transition font-medium flex items-center gap-1"
                title="Orijinal Boyutta Yeni Sekmede Aç"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Tam Boyut
            </a>
        </div>
    @else
        <div class="w-full py-12 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-4xl mb-3 shadow-inner">
                🔧
            </div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Bu parçaya ait fotoğraf bulunmuyor.</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Düzenleme sayfasından fotoğraf yükleyebilirsiniz.</p>
        </div>
    @endif
</div>
