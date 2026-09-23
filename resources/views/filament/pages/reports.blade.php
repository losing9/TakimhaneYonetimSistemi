<x-filament-panels::page>

    {{-- Sekme Navigasyonu --}}
    <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
        @foreach([
            ['key' => 'daily',     'label' => '📋 Günlük Log',        'color' => 'blue'],
            ['key' => 'overdue',   'label' => '🚨 Gecikme Raporu',    'color' => 'red'],
            ['key' => 'personnel', 'label' => '👤 Personel Bazlı',    'color' => 'purple'],
            ['key' => 'tool',      'label' => '🔧 Parça Geçmişi',     'color' => 'green'],
            ['key' => 'archive',   'label' => '🗂️ Yıllık Arşiv',      'color' => 'amber'],
        ] as $tab)
        <button
            wire:click="setTab('{{ $tab['key'] }}')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                {{ $activeTab === $tab['key']
                    ? 'bg-primary-600 text-white shadow-sm'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}"
        >
            {{ $tab['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ====================================================================== --}}
    {{-- TAB 1: Günlük İşlem Logu --}}
    {{-- ====================================================================== --}}
    @if($activeTab === 'daily')
    <div class="space-y-4">

        {{-- Filtreler --}}
        <div class="flex flex-wrap gap-4 items-end bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Başlangıç Tarihi</label>
                <input type="date" wire:model.live="fromDate"
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Bitiş Tarihi</label>
                <input type="date" wire:model.live="toDate"
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
            </div>
            <div class="flex gap-2 ml-auto">
                <a href="{{ route('export.report', ['from' => $fromDate, 'to' => $toDate, 'format' => 'pdf']) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    📄 PDF İndir
                </a>
                <a href="{{ route('export.report', ['from' => $fromDate, 'to' => $toDate, 'format' => 'xlsx']) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    📊 Excel İndir
                </a>
            </div>
        </div>

        {{-- İstatistik Kartları --}}
        @php $stats = $this->getDailyStats() @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['total'] }}</div>
                <div class="text-xs text-blue-600 mt-1">Toplam Zimmet</div>
            </div>
            <div class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['returned'] }}</div>
                <div class="text-xs text-green-600 mt-1">İade Edilen</div>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $stats['active'] }}</div>
                <div class="text-xs text-amber-600 mt-1">Hâlâ Ödünçte</div>
            </div>
            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['overdue'] }}</div>
                <div class="text-xs text-red-600 mt-1">Gecikmiş</div>
            </div>
        </div>

        {{-- Tablo --}}
        @php $loans = $this->getDailyLoans() @endphp
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Parça</th>
                        <th class="px-4 py-3 text-left">Personel</th>
                        <th class="px-4 py-3 text-left">Sicil</th>
                        <th class="px-4 py-3 text-left">Departman</th>
                        <th class="px-4 py-3 text-left">Ödünç Tarihi</th>
                        <th class="px-4 py-3 text-left">Plan. İade</th>
                        <th class="px-4 py-3 text-left">Gerç. İade</th>
                        <th class="px-4 py-3 text-left">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($loans as $loan)
                    <tr class="{{ $loan->status === 'overdue' ? 'bg-red-50 dark:bg-red-950/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }} transition-colors">
                        <td class="px-4 py-3 font-medium">{{ $loan->tool?->name ?? 'Silinmiş Parça' }}</td>
                        <td class="px-4 py-3">{{ $loan->personnel?->name ?? ($loan->loanedByUser?->name ?? 'Bilinmeyen Kullanıcı') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono">{{ $loan->personnel?->badge_number ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $loan->personnel?->department ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->loaned_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->planned_return_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->returned_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($loan->status === 'returned') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400
                                @elseif($loan->status === 'overdue') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400
                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400 @endif">
                                {{ $loan->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Bu tarih aralığında kayıt bulunamadı.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ====================================================================== --}}
    {{-- TAB 2: Gecikme Raporu --}}
    {{-- ====================================================================== --}}
    @if($activeTab === 'overdue')
    @php $overdueLoans = $this->getOverdueLoans() @endphp
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></div>
                <h2 class="text-lg font-semibold text-red-700 dark:text-red-400">
                    {{ $overdueLoans->count() }} Gecikmiş Zimmet
                </h2>
            </div>
            <a href="{{ route('export.report', ['from' => now()->subYear()->toDateString(), 'to' => now()->toDateString(), 'format' => 'pdf', 'title' => 'Gecikme Raporu']) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                📄 Gecikme Raporunu PDF İndir
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-red-200 dark:border-red-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-red-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Parça</th>
                        <th class="px-4 py-3 text-left">Personel</th>
                        <th class="px-4 py-3 text-left">Sicil No</th>
                        <th class="px-4 py-3 text-left">Departman</th>
                        <th class="px-4 py-3 text-left">İade Edilmeliydi</th>
                        <th class="px-4 py-3 text-center">Gecikme</th>
                        <th class="px-4 py-3 text-left">Notlar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-100 dark:divide-red-900/30">
                    @forelse($overdueLoans as $loan)
                    <tr class="bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-950/30 transition-colors">
                        <td class="px-4 py-3 font-semibold text-red-900 dark:text-red-300">{{ $loan->tool?->name ?? 'Silinmiş Parça' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $loan->personnel?->name ?? ($loan->loanedByUser?->name ?? 'Bilinmeyen Kullanıcı') }}</td>
                        <td class="px-4 py-3"><span class="font-mono text-xs bg-red-100 dark:bg-red-900/40 px-2 py-0.5 rounded">{{ $loan->personnel?->badge_number ?? '—' }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->personnel?->department ?? '—' }}</td>
                        <td class="px-4 py-3 text-red-700 dark:text-red-400 font-medium">{{ $loan->planned_return_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-3 py-1 bg-red-600 text-white rounded-full text-xs font-bold">
                                {{ $loan->overdue_days }} gün
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $loan->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-green-600 font-medium">✅ Gecikmiş zimmet yok!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ====================================================================== --}}
    {{-- TAB 3: Personel Bazlı --}}
    {{-- ====================================================================== --}}
    @if($activeTab === 'personnel')
    <div class="space-y-4">
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Personel Seçin</label>
            <select wire:model.live="personnelId"
                class="w-full max-w-md px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
                <option value="">-- Personel Seçin --</option>
                @foreach(\App\Models\Personnel::orderBy('name')->get() as $p)
                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->badge_number }})</option>
                @endforeach
            </select>
        </div>

        @if($personnelId)
        @php
            $personnelLoans = $this->getPersonnelLoans();
            $person = \App\Models\Personnel::find($personnelId);
        @endphp

        {{-- Personel Kartı --}}
        <div class="flex items-center gap-4 bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4">
            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white text-lg font-bold">
                {{ mb_substr($person->name, 0, 1) }}
            </div>
            <div>
                <div class="font-bold text-purple-900 dark:text-purple-300">{{ $person->name }}</div>
                <div class="text-sm text-purple-700 dark:text-purple-400">{{ $person->badge_number }} — {{ $person->department ?? 'Departman belirsiz' }}</div>
                <div class="text-xs text-purple-500 mt-1">
                    Toplam {{ $personnelLoans->count() }} zimmet |
                    Aktif: {{ $personnelLoans->whereIn('status', ['active','overdue'])->count() }} |
                    Gecikmiş: {{ $personnelLoans->where('status', 'overdue')->count() }}
                </div>
            </div>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('labels.personnel.pdf', $person) }}" target="_blank"
                   class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg">
                    🪪 Kimlik Kartı PDF
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-purple-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Parça</th>
                        <th class="px-4 py-3 text-left">Ödünç Tarihi</th>
                        <th class="px-4 py-3 text-left">Plan. İade</th>
                        <th class="px-4 py-3 text-left">Gerç. İade</th>
                        <th class="px-4 py-3 text-center">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($personnelLoans as $loan)
                    <tr class="{{ $loan->status === 'overdue' ? 'bg-red-50 dark:bg-red-950/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                        <td class="px-4 py-3 font-medium">{{ $loan->tool?->name ?? 'Silinmiş Parça' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->loaned_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->planned_return_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->returned_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($loan->status === 'returned') bg-green-100 text-green-800
                                @elseif($loan->status === 'overdue') bg-red-100 text-red-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ $loan->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Bu personele ait zimmet bulunamadı.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- ====================================================================== --}}
    {{-- TAB 4: Parça Geçmişi --}}
    {{-- ====================================================================== --}}
    @if($activeTab === 'tool')
    <div class="space-y-4">
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Parça Seçin</label>
            <select wire:model.live="toolId"
                class="w-full max-w-md px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
                <option value="">-- Parça Seçin --</option>
                @foreach(\App\Models\Tool::orderBy('name')->get() as $t)
                <option value="{{ $t->id }}">{{ $t->name }} {{ $t->serial_no ? "[$t->serial_no]" : '' }}</option>
                @endforeach
            </select>
        </div>

        @if($toolId)
        @php
            $toolHistory = $this->getToolHistory();
            $toolStats   = $this->getToolStats();
            $selectedTool = \App\Models\Tool::find($toolId);
        @endphp

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
                <div class="text-xl font-bold text-green-700">{{ $toolStats['total_loans'] ?? 0 }}</div>
                <div class="text-xs text-green-600">Toplam Ödünç</div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-center">
                <div class="text-xl font-bold text-blue-700">{{ $toolStats['total_days'] ?? 0 }}</div>
                <div class="text-xs text-blue-600">Toplam Dışarıda Gün</div>
            </div>
            <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 rounded-xl p-4 text-center">
                <div class="text-xl font-bold text-red-700">{{ $toolStats['overdue_count'] ?? 0 }}</div>
                <div class="text-xs text-red-600">Gecikme Sayısı</div>
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <a href="{{ route('labels.tool.pdf', $selectedTool) }}" target="_blank"
               class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg">
                🔖 Parça Etiketi PDF
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-green-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Personel</th>
                        <th class="px-4 py-3 text-left">Sicil No</th>
                        <th class="px-4 py-3 text-left">Ödünç Tarihi</th>
                        <th class="px-4 py-3 text-left">Plan. İade</th>
                        <th class="px-4 py-3 text-left">Gerç. İade</th>
                        <th class="px-4 py-3 text-center">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($toolHistory as $loan)
                    <tr class="{{ $loan->status === 'overdue' ? 'bg-red-50 dark:bg-red-950/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                        <td class="px-4 py-3 font-medium">{{ $loan->personnel?->name ?? ($loan->loanedByUser?->name ?? 'Bilinmeyen Kullanıcı') }}</td>
                        <td class="px-4 py-3"><span class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $loan->personnel?->badge_number ?? '—' }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->loaned_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->planned_return_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $loan->returned_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($loan->status === 'returned') bg-green-100 text-green-800
                                @elseif($loan->status === 'overdue') bg-red-100 text-red-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ $loan->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Bu parça için zimmet geçmişi bulunamadı.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- ====================================================================== --}}
    {{-- TAB 5: Yıllık Arşiv --}}
    {{-- ====================================================================== --}}
    @if($activeTab === 'archive')
    <div class="space-y-4">
        <div class="flex flex-wrap gap-4 items-end bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Yıl Seçin</label>
                <select wire:model.live="archiveYear"
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
                    @foreach(range(now()->year, max(2020, now()->year - 10)) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 ml-auto">
                <a href="{{ route('export.yearly', ['year' => $archiveYear, 'format' => 'pdf']) }}"
                   target="_blank"
                   class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                    📄 Yıllık PDF
                </a>
                <a href="{{ route('export.yearly', ['year' => $archiveYear, 'format' => 'xlsx']) }}"
                   target="_blank"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                    📊 Yıllık Excel
                </a>
            </div>
        </div>

        @php $archiveReports = $this->getArchiveReports() @endphp

        @if($archiveReports->count() > 0)
        {{-- Yıl Özeti --}}
        <div class="grid grid-cols-4 gap-3">
            <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-amber-700">{{ $archiveReports->sum('total_loans') }}</div>
                <div class="text-xs text-amber-600">Yıllık Toplam Zimmet</div>
            </div>
            <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-green-700">{{ $archiveReports->sum('total_returns') }}</div>
                <div class="text-xs text-green-600">Yıllık Toplam İade</div>
            </div>
            <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-red-700">{{ $archiveReports->sum('overdue_count') }}</div>
                <div class="text-xs text-red-600">Toplam Gecikme Yaşandı</div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-blue-700">{{ $archiveReports->count() }}</div>
                <div class="text-xs text-blue-600">Arşivlenmiş Gün</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-amber-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tarih</th>
                        <th class="px-4 py-3 text-center">Zimmet</th>
                        <th class="px-4 py-3 text-center">İade</th>
                        <th class="px-4 py-3 text-center">Gecikmiş</th>
                        <th class="px-4 py-3 text-center">Yeni Parça</th>
                        <th class="px-4 py-3 text-center">Oluşturulma</th>
                        <th class="px-4 py-3 text-center">İndir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($archiveReports as $report)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-4 py-3 font-medium">{{ $report->formatted_date }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs font-medium">{{ $report->total_loans }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs font-medium">{{ $report->total_returns }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 {{ $report->overdue_count > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }} rounded text-xs font-medium">
                                {{ $report->overdue_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $report->new_tools }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $report->generated_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-1 justify-center">
                                @if($report->hasPdf())
                                <a href="{{ route('export.report', ['from' => $report->report_date->toDateString(), 'to' => $report->report_date->toDateString(), 'format' => 'pdf', 'title' => $report->formatted_date . ' Günlük Rapor']) }}"
                                   target="_blank"
                                   class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs rounded">
                                    PDF
                                </a>
                                @endif
                                @if($report->hasXlsx())
                                <a href="{{ route('export.report', ['from' => $report->report_date->toDateString(), 'to' => $report->report_date->toDateString(), 'format' => 'xlsx']) }}"
                                   target="_blank"
                                   class="px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 text-xs rounded">
                                    XLS
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12 text-gray-400">
            <div class="text-4xl mb-3">🗂️</div>
            <div class="text-lg font-medium">{{ $archiveYear }} yılına ait arşiv bulunamadı.</div>
            <div class="text-sm mt-1">Günlük arşivler otomatik olarak her gece oluşturulur.</div>
        </div>
        @endif
    </div>
    @endif

</x-filament-panels::page>
