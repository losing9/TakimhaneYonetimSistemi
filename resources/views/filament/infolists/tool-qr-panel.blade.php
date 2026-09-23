@php
    use App\Services\QrCodeService;
    $record  = $getRecord();
    $qrSvc   = app(QrCodeService::class);
    $qrUri   = $qrSvc->forTool($record);
@endphp

<div class="flex flex-col items-center gap-4 py-2">

    {{-- QR Kod SVG --}}
    <div class="p-3 bg-white rounded-xl border-2 border-gray-200 shadow-sm">
        <img
            src="{{ $qrUri }}"
            alt="QR Kod - {{ $record->serial_no }}"
            class="w-40 h-40 object-contain"
        />
    </div>

    {{-- Seri No --}}
    <div class="text-center">
        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Seri No</div>
        <div class="font-mono text-sm font-bold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg select-all">
            {{ $record->serial_no ?? '—' }}
        </div>
    </div>

    {{-- Barkod değeri --}}
    @if($record->barcode && $record->barcode !== $record->serial_no)
    <div class="text-center">
        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Barkod</div>
        <div class="font-mono text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 px-3 py-1 rounded-lg">
            {{ $record->barcode }}
        </div>
    </div>
    @endif

    {{-- PDF İndir butonu --}}
    <a
        href="{{ route('labels.tool.pdf', $record) }}"
        target="_blank"
        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm w-full justify-center"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Etiket PDF İndir
    </a>

    {{-- Oluşturulma tarihi --}}
    <div class="text-xs text-gray-400 text-center">
        Oluşturulma: {{ $record->created_at->format('d.m.Y') }}
    </div>

</div>
