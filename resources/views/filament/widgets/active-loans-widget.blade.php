<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Başlık ve Canlı Arama --}}
        <x-slot name="heading">
            <div class="flex items-center justify-between gap-4 flex-wrap w-full">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">📋</span>
                    <span class="font-bold text-base md:text-lg text-gray-950 dark:text-white">
                        @if($this->isToday)
                            Günlük Zimmet & İade Hareketleri Tablosu
                        @else
                            {{ $this->selectedCarbonDate->format('d.m.Y') }} Tarihli Zimmet Arşivi
                        @endif
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                        {{ count($this->groupedLoans) }} Personel
                    </span>
                </div>

                {{-- Canlı Arama Inputu --}}
                <div class="w-full sm:w-72">
                    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                        <x-filament::input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Personel veya parça ara..."
                        />
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-slot>

        <x-slot name="description">
            Günlük tüm hareketler zaman damgalarıyla listelenir & A4 Yatay gün sonu çıktıları
        </x-slot>

        {{-- TARİH ARŞİVİ, GÜN SONU ÇIKTILARI VE YENİ ZİMMET ÇUBUĞU --}}
        <div class="mb-4 p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 shadow-xs flex flex-wrap items-center justify-between gap-3">
            {{-- Tarih Seçici --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">📅 Tarih / Arşiv:</span>
                <input
                    type="date"
                    wire:model.live="date"
                    class="rounded-lg text-xs py-1.5 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-xs font-semibold focus:ring-2 focus:ring-primary-500"
                />

                @if(!$this->isToday)
                    <x-filament::button
                        size="xs"
                        color="gray"
                        icon="heroicon-m-arrow-path"
                        wire:click="resetDateToToday"
                    >
                        Bugüne Dön
                    </x-filament::button>
                @endif
            </div>

            {{-- Çıktı & Hızlı Zimmet Butonları --}}
            <div class="flex items-center gap-2.5 flex-wrap">
                {{-- Gün Sonu PDF İndir (A4 Yatay) --}}
                <a
                    href="{{ route('admin-portal.export-daily-pdf', ['date' => $this->date ?: today()->toDateString()]) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-red-600 hover:bg-red-500 active:scale-95 text-white shadow-sm transition-all"
                >
                    <x-filament::icon icon="heroicon-m-document-arrow-down" class="w-4 h-4" />
                    <span>Gün Sonu PDF (A4 Yatay)</span>
                </a>

                {{-- Gün Sonu Excel İndir (A4 Yatay) --}}
                <a
                    href="{{ route('admin-portal.export-daily-excel', ['date' => $this->date ?: today()->toDateString()]) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white shadow-sm transition-all"
                >
                    <x-filament::icon icon="heroicon-m-table-cells" class="w-4 h-4" />
                    <span>Gün Sonu Excel (A4 Yatay)</span>
                </a>

                {{-- Yeni Parça Zimmetle --}}
                <x-filament::button
                    size="sm"
                    color="primary"
                    icon="heroicon-m-plus"
                    wire:click="openAssignModal"
                >
                    Yeni Parça Zimmetle
                </x-filament::button>
            </div>
        </div>

        {{-- DURUM FİLTRESİ SEKMELERİ --}}
        <div class="mb-4 flex items-center gap-2 flex-wrap">
            <button
                type="button"
                wire:click="$set('statusFilter', 'all')"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'all' ? 'bg-primary-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}"
            >
                Tümü ({{ $this->dailyStats['total'] }})
            </button>
            <button
                type="button"
                wire:click="$set('statusFilter', 'active')"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'active' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}"
            >
                🔧 Dışarıda Olanlar ({{ $this->dailyStats['active'] }})
            </button>
            <button
                type="button"
                wire:click="$set('statusFilter', 'returned')"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'returned' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}"
            >
                ✅ İade Edilenler ({{ $this->dailyStats['returned'] }})
            </button>
            @if($this->dailyStats['overdue'] > 0)
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'overdue')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'overdue' ? 'bg-red-600 text-white shadow-xs' : 'bg-red-50 dark:bg-red-950 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }}"
                >
                    🚨 Gecikenler ({{ $this->dailyStats['overdue'] }})
                </button>
            @endif
        </div>

        @if(!$this->isToday)
            <div class="mb-4 p-3 rounded-lg bg-warning-50 dark:bg-warning-950/30 border border-warning-200 dark:border-warning-800 text-xs text-warning-800 dark:text-warning-300 flex items-center justify-between">
                <span>📂 <b>{{ $this->selectedCarbonDate->format('d.m.Y') }}</b> gününe ait arşiv kayıtları listeleniyor.</span>
                <button wire:click="resetDateToToday" class="font-bold underline hover:text-warning-950">Bugünkü Duruma Dön</button>
            </div>
        @endif

        {{-- ZİMMET LİSTESİ --}}
        @if($this->groupedLoans->isEmpty())
            <div class="py-12 text-center rounded-xl border border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30">
                <div class="text-4xl mb-2">✅</div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    @if($this->isToday)
                        Seçilen filtreye uygun parça hareketi bulunamadı
                    @else
                        Bu tarihte ({{ $this->selectedCarbonDate->format('d.m.Y') }}) herhangi bir zimmet hareketi bulunamadı
                    @endif
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tüm aletler depoda ve raflarda hazır bekliyor.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($this->groupedLoans as $group)
                    <div class="rounded-xl border {{ $group['has_overdue'] ? 'border-danger-400 dark:border-danger-800 bg-danger-50/20 dark:bg-danger-950/20' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900' }} shadow-xs overflow-hidden transition-all duration-200">
                        
                        {{-- Personel Başlık Çubuğu --}}
                        <div class="px-4 py-3 bg-gray-50/90 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-sm {{ $group['has_overdue'] ? 'bg-danger-100 text-danger-700 dark:bg-danger-950 dark:text-danger-400' : 'bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-400' }}">
                                    👤
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-gray-950 dark:text-white flex items-center gap-2">
                                        <span>{{ $group['personnel_name'] }}</span>
                                        @if($group['badge_number'] !== '—')
                                            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-semibold">
                                                Sicil: {{ $group['badge_number'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $group['personnel_dept'] }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                @if($group['has_overdue'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-danger-100 text-danger-700 dark:bg-danger-950 dark:text-danger-300 border border-danger-200 dark:border-danger-800 animate-pulse">
                                        🚨 Gecikmiş Parça Var
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    Toplam {{ $group['total_count'] }} Parça ({{ $group['active_count'] }} Aktif, {{ $group['returned_count'] }} İade)
                                </span>

                                {{-- Bu personele parça ver --}}
                                @if($group['personnel_id'])
                                    <x-filament::button
                                        size="xs"
                                        color="primary"
                                        icon="heroicon-m-plus"
                                        wire:click="openAssignModal({{ $group['personnel_id'] }})"
                                    >
                                        Parça Ver
                                    </x-filament::button>
                                @endif

                                @if($group['active_count'] > 1)
                                    <x-filament::button
                                        size="xs"
                                        color="warning"
                                        icon="heroicon-m-arrow-path"
                                        wire:click="returnAllLoansForPersonnel({{ $group['personnel_id'] ?? 'null' }})"
                                        wire:confirm="Bu personelin elindeki TÜM ({{ $group['active_count'] }}) aktif parçaları teslim almak istiyor musunuz?"
                                    >
                                        Tümünü İade Al
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>

                        {{-- Personelin Zimmetindeki Aletler Tablosu --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Alet & Seri No</th>
                                        <th scope="col" class="px-4 py-3">Kayıtlı Konum</th>
                                        <th scope="col" class="px-4 py-3">Teslim Zaman Damgası</th>
                                        <th scope="col" class="px-4 py-3">İade Durumu & Zamanı</th>
                                        <th scope="col" class="px-4 py-3 text-right">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 bg-transparent">
                                    @foreach($group['loans'] as $loan)
                                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors {{ $loan->status === 'returned' ? 'opacity-85' : '' }}">
                                            {{-- Alet Adı ve Seri Numarası --}}
                                            <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                                                <div class="flex items-center gap-3">
                                                    @if($loan->tool?->image)
                                                        <img src="{{ Storage::url($loan->tool->image) }}" alt="{{ $loan->tool->name }}"
                                                             onclick="openImageLightbox('{{ Storage::url($loan->tool->image) }}', '{{ addslashes($loan->tool->name) }}')"
                                                             class="w-10 h-10 object-cover rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700 hover:scale-105 transition-transform"
                                                             title="Resmi büyütmek için tıklayın">
                                                    @endif
                                                    <div>
                                                        <div class="font-bold text-xs md:text-sm">
                                                            {{ $loan->tool?->name ?? 'Silinmiş Alet' }}
                                                        </div>
                                                        <div class="font-mono text-[11px] text-primary-600 dark:text-primary-400 mt-0.5 font-semibold">
                                                            SN: {{ $loan->tool?->serial_no ?? '—' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Konum --}}
                                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">
                                                @if($loan->tool?->slot)
                                                    <span class="inline-flex items-center gap-1 font-mono text-[11px] bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-md font-semibold border border-gray-200 dark:border-gray-700">
                                                        📍 {{ $loan->tool->slot->shelf?->block?->name ?? '' }} / R{{ $loan->tool->slot->shelf?->shelf_number ?? '' }} / G{{ $loan->tool->slot->slot_number ?? '' }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            {{-- Teslim Zaman Damgası (Saniyeli) --}}
                                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 whitespace-nowrap font-medium">
                                                ⏱️ {{ $loan->loaned_at?->format('d.m.Y H:i:s') ?? '—' }}
                                            </td>

                                            {{-- İade & Gecikme Durumu --}}
                                            <td class="px-4 py-3.5 whitespace-nowrap">
                                                @if($loan->status === 'returned')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                        ✅ İade Edildi ({{ $loan->returned_at?->format('H:i:s') }})
                                                    </span>
                                                @elseif($loan->isOverdue() || $loan->status === 'overdue')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-200 dark:border-red-800">
                                                        🚨 {{ $loan->overdue_days }} gün gecikti ({{ $loan->planned_return_at?->format('d.m') }})
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        ⏳ {{ now()->diffInDays($loan->planned_return_at, false) }} gün kaldı ({{ $loan->planned_return_at?->format('d.m') }})
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- İade Al Butonu --}}
                                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                                @if($loan->status !== 'returned')
                                                    <x-filament::button
                                                        size="xs"
                                                        color="success"
                                                        icon="heroicon-m-check-circle"
                                                        wire:click="returnSingleLoan({{ $loan->id }})"
                                                        wire:confirm="'{{ addslashes($loan->tool?->name ?? 'Alet') }}' adlı parçayı teslim almak (iade) istiyor musunuz?"
                                                    >
                                                        İade Al
                                                    </x-filament::button>
                                                @else
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs">✓ Teslim Alındı</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ZİMMET VERME MODALI --}}
        @if($showAssignModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-xs">
                <div class="w-full max-w-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl p-6 overflow-hidden space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3">
                        <h3 class="font-bold text-base text-gray-950 dark:text-white flex items-center gap-2">
                            <span>➕</span>
                            <span>Zaman Damgalı Parça Zimmetle</span>
                        </h3>
                        <button wire:click="closeAssignModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg font-bold">✕</button>
                    </div>

                    <div class="space-y-4">
                        {{-- Personel Seçimi --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">👤 Personel Seçin</label>
                            <select wire:model="assignPersonnelId" class="w-full rounded-lg text-xs py-2.5 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="">-- Personel Seçin --</option>
                                @foreach($this->personnelList as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->badge_number }} - {{ $p->department ?? 'Genel' }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Parça Seçimi --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">🔧 Parça / Alet Seçin (Müsait Olanlar)</label>
                            <select wire:model="assignToolId" class="w-full rounded-lg text-xs py-2.5 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="">-- Parça Seçin ({{ count($this->availableTools) }} adet hazır) --</option>
                                @foreach($this->availableTools as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->name }} [SN: {{ $t->serial_no ?? 'Yok' }}]
                                        @if($t->slot) - ({{ $t->slot->shelf?->block?->name ?? '' }} / R{{ $t->slot->shelf?->shelf_number ?? '' }} / G{{ $t->slot->slot_number ?? '' }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- İade Süresi --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">⏳ İade Süresi (Gün)</label>
                            <input type="number" wire:model="assignDays" min="1" max="90" class="w-full rounded-lg text-xs py-2.5 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                        </div>

                        {{-- Notlar --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">📝 Zimmet Notu (İsteğe bağlı)</label>
                            <textarea wire:model="assignNotes" rows="2" placeholder="Kullanım amacı veya iş emri..." class="w-full rounded-lg text-xs py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white resize-none"></textarea>
                        </div>

                        {{-- Zaman Damgası Bilgisi --}}
                        <div class="p-3 rounded-lg bg-primary-50 dark:bg-primary-950/30 border border-primary-200 dark:border-primary-800 text-xs text-primary-700 dark:text-primary-300">
                            ⏱️ <b>Zaman Damgası:</b> Parça kaydedildiği anda tam saniyesiyle sisteme işlenir.
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-200 dark:border-gray-800">
                        <x-filament::button color="gray" wire:click="closeAssignModal">
                            İptal
                        </x-filament::button>
                        <x-filament::button color="success" icon="heroicon-m-check" wire:click="assignLoan">
                            Zimmeti Kaydet
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
