<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Slot;
use App\Models\Station;
use App\Models\Tool;
use App\Services\QrCodeService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class LabelController extends Controller
{
    public function __construct(
        private QrCodeService $qrService,
        private ReportService $reportService,
    ) {}

    // -------------------------------------------------------------------------
    // Parça Etiketleri
    // -------------------------------------------------------------------------

    /**
     * Tek parça PDF etiketi
     */
    public function toolPdf(Tool $tool)
    {
        $tools   = collect([$tool]);
        $qrCodes = [$tool->id => $this->qrService->forTool($tool)];

        $pdf = Pdf::loadView('labels.tool_label', compact('tools', 'qrCodes'))
            ->setPaper([0, 0, 170.07, 85.03], 'portrait');

        $filename = $tool->barcode ?? $tool->id;
        return $pdf->download("etiket-{$filename}.pdf");
    }

    /**
     * Toplu parça PDF etiketi
     * ?ids=1,2,3 veya ?all=1
     */
    public function toolsBulkPdf(Request $request)
    {
        $query = Tool::query();

        if ($request->has('ids')) {
            $ids = explode(',', $request->input('ids'));
            $query->whereIn('id', $ids);
        }

        $tools = $query->get();

        $qrCodes = [];
        foreach ($tools as $tool) {
            $qrCodes[$tool->id] = $this->qrService->forTool($tool);
        }

        $pdf = Pdf::loadView('labels.tool_label', compact('tools', 'qrCodes'))
            ->setPaper([0, 0, 170.07, 85.03], 'portrait');

        return $pdf->download('parcalar-toplu-etiket.pdf');
    }

    // -------------------------------------------------------------------------
    // Personel Kartları
    // -------------------------------------------------------------------------

    /**
     * Tek personel kimlik kartı
     */
    public function personnelPdf(Personnel $personnel)
    {
        $personnelList = collect([$personnel]);
        $qrCodes = [$personnel->id => $this->qrService->forPersonnel($personnel)];

        $pdf = Pdf::loadView('labels.personnel_card', compact('personnelList', 'qrCodes'))
            ->setPaper([0, 0, 280, 180], 'portrait');

        return $pdf->download("kimlik-{$personnel->badge_number}.pdf");
    }

    /**
     * Toplu personel kartı PDF
     */
    public function personnelBulkPdf(Request $request)
    {
        $personnelList = Personnel::active()->get();

        $qrCodes = [];
        foreach ($personnelList as $person) {
            $qrCodes[$person->id] = $this->qrService->forPersonnel($person);
        }

        $pdf = Pdf::loadView('labels.personnel_card', compact('personnelList', 'qrCodes'))
            ->setPaper([0, 0, 280, 180], 'portrait');

        return $pdf->download('tum-personel-kartlari.pdf');
    }

    // -------------------------------------------------------------------------
    // Raf Etiketleri
    // -------------------------------------------------------------------------

    /**
     * Tek göz etiketi
     */
    public function slotPdf(Slot $slot)
    {
        $slot->load(['shelf.block', 'tools']);
        $slots   = collect([$slot]);
        $qrCodes = [$slot->id => $this->qrService->forSlot($slot)];

        $pdf = Pdf::loadView('labels.shelf_label', compact('slots', 'qrCodes'))
            ->setPaper([0, 0, 240, 160], 'portrait');

        return $pdf->download("raf-etiketi-{$slot->id}.pdf");
    }

    /**
     * Tüm gözlerin etiketleri
     */
    public function allSlotsPdf()
    {
        $slots = Slot::with(['shelf.block', 'tools'])->get();

        $qrCodes = [];
        foreach ($slots as $slot) {
            $qrCodes[$slot->id] = $this->qrService->forSlot($slot);
        }

        $pdf = Pdf::loadView('labels.shelf_label', compact('slots', 'qrCodes'))
            ->setPaper([0, 0, 240, 160], 'portrait');

        return $pdf->download('tum-raf-etiketleri.pdf');
    }

    // -------------------------------------------------------------------------
    // Rapor Export'ları
    // -------------------------------------------------------------------------

    /**
     * Tarih aralığı için rapor export
     * GET /export/report?from=2024-01-01&to=2024-01-31&format=pdf|xlsx
     */
    public function exportReport(Request $request)
    {
        $from   = Carbon::parse($request->input('from', now()->startOfMonth()));
        $to     = Carbon::parse($request->input('to', now()->endOfDay()));
        $format = $request->input('format', 'pdf');
        $title  = $request->input('title', 'Zimmet Raporu');

        if ($format === 'xlsx') {
            $path = $this->reportService->generateXlsx($from, $to, $title);
            return response()->download(Storage::path($path))->deleteFileAfterSend(false);
        }

        $pdf = $this->reportService->generatePdf($from, $to, $title);
        return $pdf->download("rapor-{$from->format('Y-m-d')}-{$to->format('Y-m-d')}.pdf");
    }

    /**
     * Yıllık arşiv raporu
     * GET /export/yearly/{year}?format=pdf|xlsx
     */
    public function exportYearly(int $year, Request $request)
    {
        $from   = Carbon::create($year)->startOfYear();
        $to     = Carbon::create($year)->endOfYear();
        $format = $request->input('format', 'pdf');

        if ($format === 'xlsx') {
            $path = $this->reportService->generateXlsx($from, $to, "{$year} Yıllık Arşiv Raporu");
            return response()->download(storage_path('app/' . $path))->deleteFileAfterSend(false);
        }

        $pdf = $this->reportService->generatePdf($from, $to, "{$year} Yıllık Arşiv Raporu");
        return $pdf->download("{$year}-yillik-rapor.pdf");
    }

    /**
     * Parça & Envanter Listesi Excel (.xlsx) Export (Fotoğrafsız, Temiz Tablo)
     * GET /export/tools/excel?ids=1,2,3
     */
    public function exportToolsExcel(Request $request)
    {
        $query = Tool::with(['slot.shelf.block', 'activeLoan.personnel'])->orderBy('name');

        if ($request->has('ids') && !empty($request->input('ids'))) {
            $ids = explode(',', $request->input('ids'));
            $query->whereIn('id', $ids);
        }

        $tools = $query->get();
        $spreadsheet  = $this->reportService->generateToolsXlsx($tools);
        $downloadName = 'parca_envanter_yedek_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $downloadName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$downloadName}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Parça & Envanter Listesi A4 Yatay PDF Export
     * GET /export/tools/pdf?ids=1,2,3
     */
    public function exportToolsPdf(Request $request)
    {
        $query = Tool::with(['slot.shelf.block', 'activeLoan.personnel'])->orderBy('name');

        if ($request->has('ids') && !empty($request->input('ids'))) {
            $ids = explode(',', $request->input('ids'));
            $query->whereIn('id', $ids);
        }

        $tools = $query->get();
        $pdf   = $this->reportService->generateToolsPdf($tools);
        $downloadName = 'parca_envanter_yedek_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($downloadName);
    }

    /**
     * İstasyon Canlı Haftalık Dinamik QR Ekranı
     * GET /stations/{station}/display
     */
    public function stationDisplay(Station $station)
    {
        $token = $station->getWeeklyDynamicToken();
        $qrUri = $this->qrService->toDataUri($token);

        return view('stations.display', compact('station', 'token', 'qrUri'));
    }
}


