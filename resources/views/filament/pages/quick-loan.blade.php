<x-filament-panels::page>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- BAŞLIK AÇIKLAMASI --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-6 p-4 bg-primary-50 dark:bg-primary-950/30 border border-primary-200 dark:border-primary-800 rounded-xl flex items-center gap-4">
        <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center text-white text-2xl shrink-0">⚡</div>
        <div>
            <h2 class="font-bold text-primary-900 dark:text-primary-200">QR Kod veya Seri No ile Anlık Zimmet</h2>
            <p class="text-sm text-primary-700 dark:text-primary-400 mt-0.5">
                QR tarayıcınızı <strong>aşağıdaki kutuya odaklayın</strong> ve parça etiketini okutun — ya da seri numarasını yazın.
                Parça otomatik bulunur, personel seçip onaylayın.
            </p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- BAŞARI MESAJI --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($successMessage)
    <div class="mb-4 p-4 bg-green-50 dark:bg-green-950/30 border border-green-300 dark:border-green-700 rounded-xl flex items-start gap-3">
        <div class="text-2xl">✅</div>
        <div class="text-green-800 dark:text-green-300 text-sm" style="line-height:1.6">
            {{ $successMessage }}
        </div>
        <button wire:click="clearSearch" class="ml-auto text-green-600 hover:text-green-800 text-xs underline shrink-0">Yeni Tarama</button>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TARAMA KUTUSU --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 border-dashed
        {{ $foundTool ? 'border-green-400 dark:border-green-600' : ($errorMessage ? 'border-red-400 dark:border-red-600' : 'border-gray-300 dark:border-gray-600') }}
        p-6 mb-4 transition-all duration-300">

        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            📷 QR Tarayıcı / Seri No
        </label>

        <div class="flex gap-3">
            <input
                type="text"
                wire:model="scanInput"
                wire:keydown.enter="searchTool"
                id="scan-input"
                autofocus
                autocomplete="off"
                placeholder="QR tarayın veya seri no yazıp Enter'a basın..."
                class="flex-1 px-4 py-3 text-lg font-mono rounded-xl border-2
                    {{ $foundTool ? 'border-green-400 bg-green-50 dark:bg-green-950/20' : ($errorMessage ? 'border-red-400 bg-red-50 dark:bg-red-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900') }}
                    text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
            />

            <button
                wire:click="searchTool"
                class="px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Ara
            </button>

            @if($scanInput || $foundTool)
            <button
                wire:click="clearSearch"
                class="px-4 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-colors"
                title="Temizle"
            >
                ✕
            </button>
            @endif
        </div>

        {{-- Hata --}}
        @if($errorMessage)
        <div class="mt-3 flex items-start gap-2 text-red-700 dark:text-red-400 text-sm">
            <span class="shrink-0">⚠️</span>
            <span>{{ $errorMessage }}</span>
        </div>
        @endif

        {{-- Loading göstergesi --}}
        <div wire:loading wire:target="searchTool" class="mt-3 text-sm text-gray-500 animate-pulse">
            🔍 Aranıyor...
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- PARÇA BULUNDU — ZIMMET FORMU --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($foundTool)
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-green-300 dark:border-green-700 overflow-hidden shadow-sm"
         x-data x-init="$nextTick(() => document.getElementById('personnel-select')?.focus())">

        {{-- Parça Bilgi Bandı --}}
        <div class="bg-green-600 px-6 py-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white text-xl">
                🔧
            </div>
            <div class="flex-1">
                <div class="text-white font-bold text-lg">{{ $foundTool->name }}</div>
                <div class="flex items-center gap-3 mt-0.5">
                    <span class="text-green-100 text-sm font-mono">{{ $foundTool->serial_no }}</span>
                    @if($foundTool->category_label)
                    <span class="px-2 py-0.5 bg-white/20 text-green-100 text-xs rounded-full">{{ $foundTool->category_label }}</span>
                    @endif
                    @if($foundTool->location_label !== '—')
                    <span class="text-green-100 text-xs">📍 {{ $foundTool->location_label }}</span>
                    @endif
                </div>
            </div>
            <div class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                ✅ Mevcut
            </div>
        </div>

        {{-- Zimmet Formu --}}
        <div class="p-6 space-y-5">

            {{-- Personel Seçimi --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    👤 Personel Seçin <span class="text-red-500">*</span>
                </label>
                <select
                    id="personnel-select"
                    wire:model="personnelId"
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition-colors"
                >
                    <option value="">-- Personel seçin --</option>
                    @foreach($this->personnelList as $person)
                    <option value="{{ $person->id }}">
                        {{ $person->name }} — {{ $person->badge_number }}
                        @if($person->department) ({{ $person->department }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Gün & Notlar --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        📅 Kaç Gün?
                    </label>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="$set('loanDays', max(1, loanDays - 1))"
                            class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 font-bold text-lg transition-colors flex items-center justify-center"
                        >−</button>

                        <input
                            type="number"
                            wire:model="loanDays"
                            min="1"
                            max="365"
                            class="flex-1 px-3 py-2 text-center text-lg font-bold rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />

                        <button
                            type="button"
                            wire:click="$set('loanDays', loanDays + 1)"
                            class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 font-bold text-lg transition-colors flex items-center justify-center"
                        >+</button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 text-center">
                        İade: <strong>{{ now()->addDays($loanDays)->format('d.m.Y') }}</strong>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        📝 Not (isteğe bağlı)
                    </label>
                    <textarea
                        wire:model="notes"
                        rows="2"
                        placeholder="Kısa not..."
                        class="w-full px-3 py-2 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm resize-none transition-colors"
                    ></textarea>
                </div>
            </div>

            {{-- Hızlı gün seçenekleri --}}
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 font-medium">Hızlı seçim:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach([1, 3, 7, 14, 30] as $d)
                    <button
                        type="button"
                        wire:click="$set('loanDays', {{ $d }})"
                        class="px-3 py-1 rounded-lg text-xs font-medium transition-colors
                            {{ $loanDays == $d
                                ? 'bg-primary-600 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                    >
                        {{ $d }} gün
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Onayla Butonu --}}
            <button
                wire:click="createLoan"
                wire:loading.attr="disabled"
                @if(!$personnelId) disabled @endif
                class="w-full py-4 rounded-xl font-bold text-lg transition-all
                    {{ $personnelId
                        ? 'bg-green-600 hover:bg-green-700 active:scale-95 text-white shadow-lg shadow-green-200 dark:shadow-green-900'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed' }}"
            >
                <span wire:loading.remove wire:target="createLoan">
                    ✅ Zimmet Ver — {{ $foundTool->name }}
                </span>
                <span wire:loading wire:target="createLoan" class="animate-pulse">
                    ⏳ İşleniyor...
                </span>
            </button>

        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- BOŞ DURUM — İPUÇLARI --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if(!$foundTool && !$errorMessage && !$successMessage)
    <div class="grid grid-cols-3 gap-4 mt-2">
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <div class="text-3xl mb-2">📷</div>
            <div class="text-xs font-medium text-gray-600 dark:text-gray-400">QR Tarayıcı</div>
            <div class="text-xs text-gray-500 mt-1">Tarayıcıyı kutuya odaklayıp etiketi okutun</div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <div class="text-3xl mb-2">⌨️</div>
            <div class="text-xs font-medium text-gray-600 dark:text-gray-400">Manuel Giriş</div>
            <div class="text-xs text-gray-500 mt-1">Seri numarasını yazıp Enter'a basın</div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <div class="text-3xl mb-2">⚡</div>
            <div class="text-xs font-medium text-gray-600 dark:text-gray-400">Anlık İşlem</div>
            <div class="text-xs text-gray-500 mt-1">Personel seç ve tek tıkla zimmet ver</div>
        </div>
    </div>
    @endif

    {{-- Arama kutusunu otomatik odakla --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            document.getElementById('scan-input')?.focus();
        });
        // Livewire sonrası da odakla
        document.addEventListener('livewire:update', () => {
            if (!document.getElementById('personnel-select') || document.getElementById('personnel-select').value === '') {
                setTimeout(() => document.getElementById('scan-input')?.focus(), 50);
            }
        });
    </script>

</x-filament-panels::page>
