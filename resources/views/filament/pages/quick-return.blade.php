<x-filament-panels::page>

    {{-- Başlık --}}
    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-500 rounded-full flex items-center justify-center text-white text-2xl shrink-0">↩️</div>
        <div>
            <h2 class="font-bold text-amber-900 dark:text-amber-200">QR Kod veya Seri No ile Anlık İade</h2>
            <p class="text-sm text-amber-700 dark:text-amber-400 mt-0.5">
                Parçanın etiketini okutun — zimmet sahibi ve detaylar otomatik görünür. Onaylayın ve iade tamamlansın.
            </p>
        </div>
    </div>

    {{-- Başarı --}}
    @if($successMessage)
    <div class="mb-4 p-4 bg-green-50 dark:bg-green-950/30 border border-green-300 dark:border-green-700 rounded-xl flex items-start gap-3">
        <div class="text-2xl">✅</div>
        <div class="text-green-800 dark:text-green-300 text-sm" style="line-height:1.6">{!! $successMessage !!}</div>
        <button wire:click="clearSearch" class="ml-auto text-green-600 hover:text-green-800 text-xs underline shrink-0">Yeni Tarama</button>
    </div>
    @endif

    {{-- Tarama Kutusu --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 border-dashed
        {{ $foundLoan ? 'border-amber-400 dark:border-amber-600' : ($errorMessage ? 'border-red-400 dark:border-red-600' : 'border-gray-300 dark:border-gray-600') }}
        p-6 mb-4 transition-all duration-300">

        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            📷 QR Tarayıcı / Seri No
        </label>

        <div class="flex gap-3">
            <input
                type="text"
                wire:model="scanInput"
                wire:keydown.enter="searchLoan"
                id="return-scan-input"
                autofocus
                autocomplete="off"
                placeholder="QR tarayın veya seri no yazıp Enter'a basın..."
                class="flex-1 px-4 py-3 text-lg font-mono rounded-xl border-2
                    {{ $foundLoan ? 'border-amber-400 bg-amber-50 dark:bg-amber-950/20' : ($errorMessage ? 'border-red-400 bg-red-50 dark:bg-red-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900') }}
                    text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors"
            />

            <button wire:click="searchLoan"
                class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Ara
            </button>

            @if($scanInput || $foundLoan)
            <button wire:click="clearSearch"
                class="px-4 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-colors">
                ✕
            </button>
            @endif
        </div>

        @if($errorMessage)
        <div class="mt-3 flex items-start gap-2 text-red-700 dark:text-red-400 text-sm">
            <span class="shrink-0">⚠️</span>
            <span>{{ $errorMessage }}</span>
        </div>
        @endif

        <div wire:loading wire:target="searchLoan" class="mt-3 text-sm text-gray-500 animate-pulse">🔍 Aranıyor...</div>
    </div>

    {{-- Zimmet Bulundu — İade Formu --}}
    @if($foundLoan)
    @php
        $tool      = $foundLoan->tool;
        $personnel = $foundLoan->personnel;
        $overdue   = $foundLoan->isOverdue();
    @endphp
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-amber-300 dark:border-amber-700 overflow-hidden shadow-sm">

        {{-- Zimmet Bilgi Bandı --}}
        <div class="bg-{{ $overdue ? 'red' : 'amber' }}-500 px-6 py-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white text-xl">
                {{ $overdue ? '🚨' : '📤' }}
            </div>
            <div class="flex-1">
                <div class="text-white font-bold text-lg">{{ $tool->name }}</div>
                <div class="flex flex-wrap items-center gap-3 mt-0.5">
                    <span class="text-white/80 text-sm font-mono">{{ $tool->serial_no }}</span>
                    <span class="text-white/80 text-sm">→ <strong class="text-white">{{ $personnel->name }}</strong> ({{ $personnel->badge_number }})</span>
                    @if($personnel->department)
                    <span class="text-white/60 text-xs">{{ $personnel->department }}</span>
                    @endif
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="text-white/80 text-xs">Plan. İade</div>
                <div class="text-white font-bold">{{ $foundLoan->planned_return_at->format('d.m.Y') }}</div>
                @if($overdue)
                <div class="px-2 py-0.5 bg-white text-red-600 text-xs font-bold rounded-full mt-1">
                    {{ $foundLoan->overdue_days }} gün gecikmeli!
                </div>
                @endif
            </div>
        </div>

        {{-- İade Formu --}}
        <div class="p-6 space-y-5">

            {{-- Konuma iade (isteğe bağlı) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    📍 Rafına Yerleştiriliyor (isteğe bağlı)
                </label>
                <select wire:model="returnSlotId"
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                    <option value="">-- Konum seçmeyin (konumsuz kalır) --</option>
                    @foreach($this->slotList as $slot)
                    <option value="{{ $slot->id }}" @if($returnSlotId == $slot->id) selected @endif>
                        {{ $slot->full_label }} ({{ $slot->tools_count }}/{{ $slot->capacity }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Not --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    📝 İade Notu (isteğe bağlı)
                </label>
                <textarea wire:model="notes" rows="2" placeholder="Hasar, eksik parça vb..."
                    class="w-full px-3 py-2 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm resize-none"></textarea>
            </div>

            {{-- Onayla --}}
            <button
                wire:click="returnLoan"
                wire:loading.attr="disabled"
                class="w-full py-4 rounded-xl font-bold text-lg bg-amber-500 hover:bg-amber-600 active:scale-95 text-white shadow-lg shadow-amber-200 dark:shadow-amber-900 transition-all"
            >
                <span wire:loading.remove wire:target="returnLoan">
                    ↩️ İadeyi Onayla — {{ $tool->name }}
                </span>
                <span wire:loading wire:target="returnLoan" class="animate-pulse">⏳ İşleniyor...</span>
            </button>

        </div>
    </div>
    @endif

    {{-- Boş durum --}}
    @if(!$foundLoan && !$errorMessage && !$successMessage)
    <div class="grid grid-cols-3 gap-4 mt-2">
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <div class="text-3xl mb-2">📷</div>
            <div class="text-xs font-medium text-gray-600 dark:text-gray-400">QR Tarayıcı</div>
            <div class="text-xs text-gray-500 mt-1">Parça etiketini okutun</div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <div class="text-3xl mb-2">👤</div>
            <div class="text-xs font-medium text-gray-600 dark:text-gray-400">Zimmet Sahibi</div>
            <div class="text-xs text-gray-500 mt-1">Otomatik görünür</div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <div class="text-3xl mb-2">✅</div>
            <div class="text-xs font-medium text-gray-600 dark:text-gray-400">Tek Tıkla İade</div>
            <div class="text-xs text-gray-500 mt-1">Konum seçip onayla</div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            document.getElementById('return-scan-input')?.focus();
        });
        document.addEventListener('livewire:update', () => {
            setTimeout(() => document.getElementById('return-scan-input')?.focus(), 50);
        });
    </script>

</x-filament-panels::page>
