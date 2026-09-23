@php
    $tools = $record->tools()->with(['activeLoan.personnel'])->orderBy('name')->get();
    $total = $tools->count();
    $capacity = $record->capacity ?: 1;
    $percent = min(100, round(($total / $capacity) * 100));
    $availableCount = $tools->where('status', 'available')->count();
    $loanedCount = $tools->where('status', 'loaned')->count();
    $maintenanceCount = $tools->where('status', 'maintenance')->count();
@endphp

<div class="space-y-4">
    {{-- Üst Bilgi Özeti --}}
    <div class="p-4 rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800/80 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <span>{{ $record->shelf?->block?->name ?? 'Blok' }}</span>
                    <span>/</span>
                    <span>{{ $record->shelf?->name ?? 'Raf' }}</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 mt-0.5">
                    <span>📦 {{ $record->name }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-mono font-medium {{ $percent >= 100 ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                        {{ $total }} / {{ $capacity }} Parça (%{{ $percent }})
                    </span>
                </h3>
            </div>

            {{-- Durum Hapları --}}
            <div class="flex items-center gap-2 text-xs font-medium">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    ✅ {{ $availableCount }} Mevcut
                </span>
                @if($loanedCount > 0)
                <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                    📤 {{ $loanedCount }} Ödünçte
                </span>
                @endif
                @if($maintenanceCount > 0)
                <span class="px-2.5 py-1 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                    🔧 {{ $maintenanceCount }} Bakımda
                </span>
                @endif
            </div>
        </div>

        {{-- Kapasite Çubuğu --}}
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-3 overflow-hidden">
            <div class="h-2 rounded-full transition-all duration-500 {{ $percent >= 100 ? 'bg-red-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                 style="width: {{ $percent }}%"></div>
        </div>
    </div>

    {{-- Takım Listesi --}}
    @if($tools->isEmpty())
        <div class="py-12 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
            <div class="text-4xl mb-2">📭</div>
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Bu gözde kayıtlı takım bulunmuyor.</p>
            <p class="text-xs text-gray-500 mt-1">Parçalar / Takımlar sayfasından takım eklerken veya düzenlerken bu gözü seçebilirsiniz.</p>
        </div>
    @else
        <div class="max-h-[60vh] overflow-y-auto space-y-2 pr-1">
            @foreach($tools as $tool)
                @php
                    $statusColor = match($tool->status) {
                        'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                        'loaned' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                        'maintenance' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800',
                        default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                    };
                    $statusLabel = match($tool->status) {
                        'available' => 'Mevcut',
                        'loaned' => 'Ödünçte',
                        'maintenance' => 'Bakımda',
                        'scrapped' => 'Hurda',
                        default => $tool->status,
                    };
                @endphp
                <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex items-center gap-3 hover:border-primary-400 dark:hover:border-primary-600 transition shadow-sm">
                    {{-- Görsel --}}
                    <div class="shrink-0">
                        @if($tool->image)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($tool->image) }}"
                                alt="{{ $tool->name }}"
                                class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 cursor-pointer hover:scale-105 transition shadow-sm"
                                onclick="window.filamentOpenLightbox ? window.filamentOpenLightbox('{{ \Illuminate\Support\Facades\Storage::url($tool->image) }}', '{{ addslashes($tool->name) }}') : window.open('{{ \Illuminate\Support\Facades\Storage::url($tool->image) }}', '_blank')"
                                title="Büyütmek için tıklayın"
                            >
                        @else
                            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xl text-gray-400">
                                🔧
                            </div>
                        @endif
                    </div>

                    {{-- Bilgiler --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-gray-900 dark:text-gray-100 truncate">
                                {{ $tool->name }}
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full border font-semibold shrink-0 {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if($tool->serial_no)
                                <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-[11px]">
                                    # {{ $tool->serial_no }}
                                </span>
                            @endif
                            @if($tool->category_label)
                                <span>• {{ $tool->category_label }}</span>
                            @endif
                        </div>

                        {{-- Zimmetli ise kimde --}}
                        @if($tool->status === 'loaned' && $tool->activeLoan)
                            <div class="mt-1.5 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5 bg-amber-50/60 dark:bg-amber-950/30 px-2 py-1 rounded-lg border border-amber-200/60 dark:border-amber-900/40">
                                <span>👤</span>
                                <span class="font-semibold">{{ $tool->activeLoan->personnel?->name ?? 'Bilinmeyen Personel' }}</span>
                                @if($tool->activeLoan->personnel?->badge_number)
                                    <span class="font-mono text-[10px]">({{ $tool->activeLoan->personnel->badge_number }})</span>
                                @endif
                                <span>• İade: {{ $tool->activeLoan->planned_return_at?->format('d.m.Y') ?? '—' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Hızlı İşlem --}}
                    <div class="shrink-0 flex items-center gap-1">
                        <a
                            href="{{ route('filament.admin.resources.tools.edit', $tool) }}"
                            target="_blank"
                            class="p-2 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
                            title="Parçayı Düzenle"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Alt Filtreleme Aksiyonu --}}
    <div class="pt-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between text-xs">
        <span class="text-gray-500">Bu gözde toplam <strong>{{ $total }}</strong> takım bulunuyor.</span>
        <a
            href="{{ route('filament.admin.resources.tools.index', ['tableFilters[slot_id][value]' => $record->id]) }}"
            class="font-semibold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1"
            target="_blank"
        >
            <span>Takımlar Tablosunda Aç</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
