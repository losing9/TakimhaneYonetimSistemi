<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Slot;
use App\Models\Tool;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminPortalController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Dashboard page — Canlı Zimmet & Günlük Arşiv
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Tarih parametresini doğrula: sadece geçerli tarih formatı kabul edilir
        $selectedDate = $request->input('date', today()->toDateString());
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = today()->toDateString();
        }
        $isToday = ($selectedDate === today()->toDateString());
        $carbonDate = Carbon::parse($selectedDate);

        // Envanter ve Zimmet istatistikleri
        $stats = [
            'total_tools'     => Tool::count(),
            'available_tools' => Tool::available()->count(),
            'loaned_tools'    => Tool::where('status', 'loaned')->count(),
            'overdue_tools'   => Tool::overdue()->count(),
            'active_loans'    => Loan::whereIn('status', ['active', 'overdue'])->count(),
        ];

        // Zimmet Sorgusu (Hem anlık dışarıda olan aktif/gecikmiş parçalar hem de seçilen tarihte verilen/iade edilen tüm hareketler)
        $query = Loan::with(['tool.slot.shelf.block', 'personnel', 'loanedByUser']);

        if ($isToday) {
            $loansForView = $query->where(function ($q) use ($carbonDate) {
                $q->whereIn('status', ['active', 'overdue'])
                  ->orWhereDate('loaned_at', $carbonDate)
                  ->orWhereDate('returned_at', $carbonDate);
            })->orderByDesc('loaned_at')->get();
        } else {
            $loansForView = $query->where(function ($q) use ($carbonDate) {
                $q->whereDate('loaned_at', $carbonDate)
                  ->orWhereDate('returned_at', $carbonDate);
            })->orderByDesc('loaned_at')->get();
        }

        // Günlük hareket istatistikleri
        $dailyStats = [
            'total_movements' => $loansForView->count(),
            'still_loaned'    => $loansForView->whereIn('status', ['active', 'overdue'])->count(),
            'returned_today'  => $loansForView->where('status', 'returned')->count(),
            'overdue_count'   => $loansForView->where('status', 'overdue')->count(),
        ];

        // Personel / Kullanıcı bazında gruplama (Aynı kullanıcı listede sadece 1 kez görünür)
        $groupedLoans = $loansForView->groupBy(function ($loan) {
            return $loan->personnel_id ? 'p_' . $loan->personnel_id : 'u_' . ($loan->loaned_by_user_id ?? 0);
        })->map(function ($loans) {
            $first = $loans->first();
            $personnelName = $first->personnel?->name ?? ($first->loanedByUser?->name ?? 'Bilinmeyen Kullanıcı');
            $personnelDept = $first->personnel?->department ?? 'Genel';
            $badgeNumber   = $first->personnel?->badge_number ?? '—';
            $hasOverdue    = $loans->contains(function ($l) {
                return $l->isOverdue() || $l->status === 'overdue';
            });
            $activeCount   = $loans->whereIn('status', ['active', 'overdue'])->count();
            $returnedCount = $loans->where('status', 'returned')->count();

            return [
                'personnel_name' => $personnelName,
                'personnel_dept' => $personnelDept,
                'badge_number'   => $badgeNumber,
                'personnel_id'   => $first->personnel_id,
                'has_overdue'    => $hasOverdue,
                'total_count'    => $loans->count(),
                'active_count'   => $activeCount,
                'returned_count' => $returnedCount,
                'loans'          => $loans,
            ];
        })->values();

        // Modal için kullanılabilir parçalar ve aktif personeller
        $availableTools = Tool::available()->orderBy('name')->get();
        $personnelList  = Personnel::active()->orderBy('name')->get();

        // Son 10 işlem geçmişi
        $recentTransactions = Loan::with(['tool', 'personnel'])
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('admin-portal.index', compact(
            'user', 'stats', 'dailyStats', 'groupedLoans', 'loansForView', 'recentTransactions',
            'selectedDate', 'isToday', 'carbonDate', 'availableTools', 'personnelList'
        ));
    }

    /**
     * POST: Zaman damgalı hızlı parça zimmetleme
     */
    public function assignLoan(Request $request)
    {

        $request->validate([
            'personnel_id' => 'required|exists:personnels,id',
            'tool_id'      => 'required|exists:tools,id',
            'days'         => 'nullable|integer|min:1|max:90',
            'notes'        => 'nullable|string|max:500',
        ]);

        $tool = Tool::findOrFail($request->tool_id);

        if ($tool->status === 'loaned') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Bu parça şu anda başka bir zimmette bulunuyor.'], 400);
            }
            return back()->with('error', 'Bu parça şu anda başka bir zimmette bulunuyor.');
        }

        $personnel = Personnel::findOrFail($request->personnel_id);
        $days = (int) ($request->input('days') ?: 7);

        $now = now(); // Saniyesine kadar hassas zaman damgası
        $plannedReturn = $now->copy()->addDays($days);

        $loan = Loan::create([
            'tool_id'           => $tool->id,
            'personnel_id'      => $personnel->id,
            'loaned_at'         => $now,
            'planned_return_at' => $plannedReturn,
            'status'            => 'active',
            'notes'             => $request->input('notes'),
            'loaned_by_user_id' => Auth::id(),
            'created_by'        => Auth::id(),
        ]);

        // Aleti ödünçte durumuna al
        $tool->update(['status' => 'loaned']);

        $msg = "✅ '{$tool->name}' parçası {$now->format('d.m.Y H:i:s')} zaman damgası ile '{$personnel->name}' personeline zimmetlendi.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'loan'    => $loan,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * GET: Seçilen güne ait zaman damgalı PDF Gün Sonu Çıktısı
     */
    public function exportDailyPdf(Request $request)
    {
        // Tarih parametresini doğrula
        $dateStr = $request->input('date', today()->toDateString());
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            $dateStr = today()->toDateString();
        }
        $date = Carbon::parse($dateStr);

        $pdf = $this->reportService->generatePdf(
            $date,
            $date,
            "Gün Sonu Zimmet Raporu (" . $date->format('d.m.Y') . ")"
        );

        return $pdf->download("gun-sonu-zimmet-raporu-{$date->format('Y-m-d')}.pdf");
    }

    /**
     * GET: Seçilen güne ait zaman damgalı Excel (XLSX) Gün Sonu Çıktısı
     */
    public function exportDailyExcel(Request $request)
    {
        // Tarih parametresini doğrula
        $dateStr = $request->input('date', today()->toDateString());
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            $dateStr = today()->toDateString();
        }
        $date = Carbon::parse($dateStr);

        $filePath = $this->reportService->generateXlsx(
            $date,
            $date,
            "Gün Sonu Zimmet Raporu (" . $date->format('d.m.Y') . ")"
        );

        $filename = "gun-sonu-zimmet-raporu-{$date->format('Y-m-d')}.xlsx";

        $fullPath = Storage::path($filePath);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $filePath);
        }

        return response()->download($fullPath, $filename)->deleteFileAfterSend();
    }

    /**
     * AJAX / POST: Yönetici tarafından tekil zimmeti iade al
     */
    public function returnLoan(Request $request, Loan $loan)
    {

        if ($loan->status === 'returned') {
            return response()->json(['success' => false, 'message' => 'Bu alet zaten daha önce iade edilmiş.'], 400);
        }

        $toolName = $loan->tool?->name ?? 'Alet';
        $personnelName = $loan->personnel?->name ?? ($loan->loanedByUser?->name ?? 'Kullanıcı');

        // Loan güncelle
        $loan->update([
            'returned_at'            => now(),
            'status'                 => 'returned',
            'return_condition_notes' => $request->input('notes', 'Yönetici portalından teslim alındı.'),
        ]);

        // Aleti müsait yap
        if ($loan->tool) {
            $loan->tool->update(['status' => 'available']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "✅ '{$toolName}' ({$personnelName}) başarıyla iade alındı ve alet boşa çıkarıldı.",
                'loan_id' => $loan->id,
            ]);
        }

        return back()->with('success', "✅ '{$toolName}' ({$personnelName}) başarıyla iade alındı.");
    }

    /**
     * Hızlı alet ekleme sayfası
     */
    public function addToolPage()
    {
        $user = Auth::user();
        $categories = Tool::CATEGORIES;
        
        // Gözleri (slots) hiyerarşik isimleriyle getir
        $slots = Slot::with('shelf.block')->get()->map(function ($slot) {
            return [
                'id' => $slot->id,
                'label' => $slot->full_label,
            ];
        });

        return view('admin-portal.add-tool', compact('user', 'categories', 'slots'));
    }

    /**
     * AJAX: Hızlı alet ekleme işlemi
     */
    public function addTool(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'category'      => 'required|string|in:' . implode(',', array_keys(Tool::CATEGORIES)),
            'slot_id'       => 'required|exists:slots,id',
            'serial_no'     => 'nullable|string|max:50',
            'max_loan_days' => 'required|integer|min:1|max:180',
            'description'   => 'nullable|string|max:500',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $serialNo = trim($request->input('serial_no', ''));
        $existingCount = 0;

        // Seri numarası girilmişse mevcut sayısını kontrol et
        if (!empty($serialNo)) {
            $existingCount = Tool::where('serial_no', $serialNo)->count();
        } else {
            // Seri numarası boşsa otomatik üret
            $serialNo = Tool::generateSerialNo();
        }


        // Fotoğraf yükleme
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('tools', 'public');
        }

        // Aleti oluştur
        $tool = Tool::create([
            'name'          => trim($request->name),
            'category'      => $request->category,
            'slot_id'       => $request->slot_id,
            'serial_no'     => $serialNo,
            'barcode'       => $serialNo,
            'max_loan_days' => $request->max_loan_days,
            'description'   => trim($request->description),
            'image'         => $imagePath,
            'status'        => 'available',
        ]);


        $warningMsg = $existingCount > 0 ? " (⚠️ Not: Aynı seri no ile {$existingCount} adet daha alet var, stok arttırıldı.)" : "";

        return response()->json([
            'success'   => true,
            'message'   => "✅ '{$tool->name}' ({$tool->serial_no}) başarıyla envantere eklendi{$warningMsg}.",
            'tool'      => [
                'id'        => $tool->id,
                'name'      => $tool->name,
                'serial_no' => $tool->serial_no,
            ]
        ]);

    }

    /**
     * AJAX: Sunucu tarafında OCR — Fotoğraftan seri numarası okuma
     * Native Tesseract OCR binary kullanır (Tesseract.js'den çok daha doğru)
     */
    public function ocrScan(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:15360',
        ]);

        $photo = $request->file('photo');
        $fullPath = $photo->getRealPath();
        $imageData = base64_encode(file_get_contents($fullPath));

        $results = [];

        try {
            // ═══════════════════════════════════════════════════════════════
            // 1. GOOGLE GEMINI MULTIMODAL AI
            // ═══════════════════════════════════════════════════════════════
            // API anahtarı yalnızca .env dosyasından alınır; asla hardcoded olmaz
            $geminiApiKey = config('services.gemini.key', '');

            if (!empty($geminiApiKey)) {
                $geminiModels = ['gemini-2.5-flash', 'gemini-1.5-flash'];
                $mimeType = $photo->getMimeType() ?: 'image/jpeg';

                foreach ($geminiModels as $model) {
                    try {
                        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($geminiApiKey);

                        $prompt = "Bu endüstriyel alet / parça veya ekipman fotoğrafındaki TÜM yazıları, marka modelleri, barkod altı sayıları ve özellikle SERİ NUMARASINI tespit et. "
                                . "Cevabını sadece ve sadece aşağıdaki gibi sade bir formatta ver:\n"
                                . "SERİ_NO: [en olası seri numarası veya parça kodu]\n"
                                . "DİĞER: [görünen tüm diğer kelimeler, sayılar ve kodlar virgülle ayrılmış]";

                        $payload = json_encode([
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => $prompt],
                                        [
                                            'inline_data' => [
                                                'mime_type' => $mimeType,
                                                'data' => $imageData
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'generationConfig' => [
                                'temperature' => 0.1,
                                'maxOutputTokens' => 300
                            ]
                        ]);

                        $ch = curl_init($url);
                        curl_setopt_array($ch, [
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => $payload,
                            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 15,
                        ]);

                        $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($response && $httpCode === 200) {
                            $json = json_decode($response, true);
                            if (!empty($json['candidates'][0]['content']['parts'][0]['text'])) {
                                $geminiText = trim($json['candidates'][0]['content']['parts'][0]['text']);
                                $results[] = ['source' => 'gemini_vision_ai', 'text' => $geminiText];
                                break; // Başarılı, döngüden çık
                            }
                        }
                    } catch (\Throwable $e) {
                        // Diğer modele geç
                    }
                }
            }

            // ═══════════════════════════════════════════════════════════════
            // 2. Native Tesseract OCR (Sunucu Yedek)
            // ═══════════════════════════════════════════════════════════════
            $tesseractBin = trim(shell_exec('which tesseract 2>/dev/null') ?? '');

            if ($tesseractBin && empty($results)) {
                $tempOcrFile = tempnam(sys_get_temp_dir(), 'ocr_') . '.jpg';
                file_put_contents($tempOcrFile, base64_decode($imageData));

                // PSM 6 (Block)
                $outputBase = tempnam(sys_get_temp_dir(), 'tess_');
                $cmd = escapeshellcmd($tesseractBin) . ' '
                     . escapeshellarg($tempOcrFile) . ' '
                     . escapeshellarg($outputBase)
                     . ' -l eng+tur --psm 6 --oem 3 2>/dev/null';
                exec($cmd);

                $outputFile = $outputBase . '.txt';
                if (file_exists($outputFile)) {
                    $text = trim(file_get_contents($outputFile));
                    if ($text) {
                        $results[] = ['source' => 'tesseract_block', 'text' => $text];
                    }
                    @unlink($outputFile);
                }
                @unlink($outputBase);

                // PSM 7 (Line)
                $outputBase2 = tempnam(sys_get_temp_dir(), 'tess2_');
                $cmd2 = escapeshellcmd($tesseractBin) . ' '
                      . escapeshellarg($tempOcrFile) . ' '
                      . escapeshellarg($outputBase2)
                      . ' -l eng+tur --psm 7 --oem 3 2>/dev/null';
                exec($cmd2);

                $outputFile2 = $outputBase2 . '.txt';
                if (file_exists($outputFile2)) {
                    $text2 = trim(file_get_contents($outputFile2));
                    if ($text2) {
                        $results[] = ['source' => 'tesseract_line', 'text' => $text2];
                    }
                    @unlink($outputFile2);
                }
                @unlink($outputBase2);

                @unlink($tempOcrFile);
            }

            // ═══════════════════════════════════════════════════════════════
            // 3. OCR.space Free API (Yedek)
            // ═══════════════════════════════════════════════════════════════
            $ocrSpaceKey = env('OCR_SPACE_API_KEY', '');
            if ($ocrSpaceKey && empty($results)) {
                try {
                    $mime = $photo->getMimeType();

                    $ch = curl_init('https://api.ocr.space/parse/image');
                    curl_setopt_array($ch, [
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => [
                            'apikey'            => $ocrSpaceKey,
                            'base64Image'       => "data:{$mime};base64,{$imageData}",
                            'language'          => 'eng',
                            'isOverlayRequired' => 'false',
                            'OCREngine'         => '2',
                            'scale'             => 'true',
                        ],
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 10,
                    ]);

                    $response = curl_exec($ch);
                    curl_close($ch);

                    if ($response) {
                        $data = json_decode($response, true);
                        if (!empty($data['ParsedResults'][0]['ParsedText'])) {
                            $apiText = trim($data['ParsedResults'][0]['ParsedText']);
                            $results[] = ['source' => 'ocr_space', 'text' => $apiText];
                        }
                    }
                } catch (\Throwable $e) {}
            }

        } finally {
            // Tamamlandı
        }

        if (empty($results)) {
            return response()->json([
                'success' => false,
                'message' => 'Metin okunamadı. Daha yakın ve net bir fotoğraf çekin.',
                'results' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
