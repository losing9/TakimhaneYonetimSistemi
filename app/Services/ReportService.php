<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\Loan;
use App\Models\Tool;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportService
{
    // -------------------------------------------------------------------------
    // PDF Rapor (A4 Yatay / Landscape Formatında)
    // -------------------------------------------------------------------------

    /**
     * Belirtilen tarih aralığı için A4 Yatay formatta PDF rapor oluştur
     */
    public function generatePdf(
        CarbonInterface $from,
        CarbonInterface $to,
        string $title = 'Zimmet Raporu'
    ): \Barryvdh\DomPDF\PDF {
        $loans = Loan::query()
            ->with(['tool.slot.shelf.block', 'personnel', 'loanedByUser'])
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('loaned_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                  ->orWhereBetween('returned_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
            })
            ->orderBy('loaned_at', 'desc')
            ->get();

        $overdueLoans = $loans->filter(function ($l) {
            return $l->status === 'overdue' || $l->isOverdue();
        });

        $stats = [
            'total_loans'   => $loans->count(),
            'total_returns' => $loans->where('status', 'returned')->count(),
            'active_loans'  => $loans->whereIn('status', ['active', 'overdue'])->count(),
            'overdue_count' => $overdueLoans->count(),
        ];

        $dateRange = $from->format('d.m.Y') === $to->format('d.m.Y')
            ? $from->format('d.m.Y')
            : $from->format('d.m.Y') . ' — ' . $to->format('d.m.Y');

        return Pdf::loadView('reports.daily_report', compact(
            'loans', 'overdueLoans', 'stats', 'title', 'dateRange'
        ))->setPaper('a4', 'landscape');
    }

    // -------------------------------------------------------------------------
    // Excel (XLSX) Rapor (A4 Yatay / Landscape Formatında)
    // -------------------------------------------------------------------------

    /**
     * Belirtilen tarih aralığı için A4 Yatay ayarlı XLSX rapor oluştur ve dosya yolunu döner
     */
    public function generateXlsx(
        CarbonInterface $from,
        CarbonInterface $to,
        string $title = 'Zimmet Raporu'
    ): string {
        $loans = Loan::query()
            ->with(['tool.slot.shelf.block', 'personnel', 'loanedByUser'])
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('loaned_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                  ->orWhereBetween('returned_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
            })
            ->orderBy('loaned_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        
        // Sayfa Yapısı: A4 Yatay (Landscape)
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Zimmet Logu');
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setShowGridlines(true);

        // ---- Üst Başlık & Zaman Damgası Banner ----
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', "🔧 TAKIMHANE YÖNETİM SİSTEMİ — " . mb_strtoupper($title, 'UTF-8'));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', "Rapor Tarihi: " . $from->format('d.m.Y') . "  |  Oluşturulma Zaman Damgası: " . now()->format('d.m.Y H:i:s') . "  |  Toplam Hareket: " . $loans->count() . "  |  İade Edilen: " . $loans->where('status', 'returned')->count() . "  |  Ödünçte: " . $loans->whereIn('status', ['active', 'overdue'])->count());
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['argb' => 'FF1E293B'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFE2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ---- Tablo Başlıkları ----
        $headers = [
            'A' => 'Sıra',
            'B' => 'Parça Adı',
            'C' => 'Seri No',
            'D' => 'Barkod / Konum',
            'E' => 'Teslim Alan Personel',
            'F' => 'Sicil No',
            'G' => 'Departman',
            'H' => 'Teslim Zaman Damgası',
            'I' => 'Planlanan İade',
            'J' => 'Gerçek İade Zamanı',
            'K' => 'Durum',
            'L' => 'Gecikme (Gün)',
            'M' => 'Notlar / Açıklama',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '4', $label);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF93C5FD']]],
        ];
        $sheet->getStyle('A4:M4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(24);

        // ---- Veri Satırları ----
        $row = 5;
        foreach ($loans as $i => $loan) {
            $isOverdue = $loan->status === 'overdue' || $loan->isOverdue();
            $slotInfo = $loan->tool?->slot ? ($loan->tool->slot->shelf?->block?->name . ' R' . $loan->tool->slot->shelf?->shelf_number . ' G' . $loan->tool->slot->slot_number) : ($loan->tool?->barcode ?? '—');

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $loan->tool?->name ?? 'Silinmiş Alet');
            $sheet->setCellValue('C' . $row, $loan->tool?->serial_no ?? '—');
            $sheet->setCellValue('D' . $row, $slotInfo);
            $sheet->setCellValue('E' . $row, $loan->personnel?->name ?? ($loan->loanedByUser?->name ?? 'Bilinmiyor'));
            $sheet->setCellValue('F' . $row, $loan->personnel?->badge_number ?? '—');
            $sheet->setCellValue('G' . $row, $loan->personnel?->department ?? '—');
            $sheet->setCellValue('H' . $row, $loan->loaned_at?->format('d.m.Y H:i:s') ?? '—');
            $sheet->setCellValue('I' . $row, $loan->planned_return_at?->format('d.m.Y H:i') ?? '—');
            $sheet->setCellValue('J' . $row, $loan->returned_at?->format('d.m.Y H:i:s') ?? '—');
            $sheet->setCellValue('K' . $row, $loan->status === 'returned' ? 'İade Edildi' : ($isOverdue ? 'Gecikmede' : 'Ödünçte'));
            $sheet->setCellValue('L' . $row, $isOverdue ? $loan->overdue_days : 0);
            $sheet->setCellValue('M' . $row, $loan->notes ?? ($loan->return_condition_notes ?? ''));

            // Satır stilleri
            $rowStyle = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
            $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($rowStyle);

            if ($isOverdue) {
                $sheet->getStyle("A{$row}:M{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFEE2E2');
            } elseif ($loan->status === 'returned') {
                $sheet->getStyle("A{$row}:M{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF0FDF4');
            } elseif ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:M{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        // Sütun genişlikleri (A4 Yatay sayfa için optimize)
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Kaydet
        $filename = 'reports/' . $from->format('Y/m') . '/' . $from->format('Y-m-d') . '_' . $to->format('Y-m-d') . '_report.xlsx';
        $tempPath = sys_get_temp_dir() . '/' . basename($filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        Storage::put($filename, file_get_contents($tempPath));
        @unlink($tempPath);

        return $filename;
    }

    // -------------------------------------------------------------------------
    // Parça Listesi (Envanter Yedek) Excel (XLSX) Export (Fotoğrafsız, Temiz Tablo)
    // -------------------------------------------------------------------------
    public function generateToolsXlsx($tools = null): Spreadsheet
    {
        if ($tools === null) {
            $tools = Tool::query()
                ->with(['slot.shelf.block', 'activeLoan.personnel'])
                ->orderBy('name')
                ->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Parca_Envanter_Yedek');
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->setShowGridlines(true);

        // Header Banner
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', "🔧 TAKIMHANE YÖNETİM SİSTEMİ — PARÇA & ENVANTER LİSTESİ (YEDEK)");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Subtitle
        $totalCount       = $tools->count();
        $availableCount   = $tools->where('status', 'available')->count();
        $loanedCount      = $tools->where('status', 'loaned')->count();
        $maintenanceCount = $tools->where('status', 'maintenance')->count();
        $scrappedCount    = $tools->where('status', 'scrapped')->count();

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', "Yedekleme Zamanı: " . now()->format('d.m.Y H:i:s') . "  |  Toplam Parça: {$totalCount}  |  Mevcut: {$availableCount}  |  Ödünçte: {$loanedCount}  |  Bakımda: {$maintenanceCount}  |  Hurda: {$scrappedCount}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['argb' => 'FF334155'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFE2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Table Headers
        $headers = [
            'A' => 'Sıra',
            'B' => 'Parça Adı',
            'C' => 'Kategori',
            'D' => 'Seri No',
            'E' => 'Barkod / QR',
            'F' => 'Konum (Göz / Raf / Blok)',
            'G' => 'Durum',
            'H' => 'Zimmetli Personel',
            'I' => 'Personel Sicil No',
            'J' => 'Maks. Ödünç Süresi (Gün)',
            'K' => 'Sisteme Eklenme Tarihi',
            'L' => 'Açıklama',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '4', $label);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFD97706']], // Kehribar/Amber
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFCD34D']]],
        ];
        $sheet->getStyle('A4:L4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(24);

        // Rows
        $row = 5;
        foreach ($tools as $i => $tool) {
            $statusLabel = match($tool->status) {
                'available'   => 'Mevcut',
                'loaned'      => 'Ödünçte',
                'maintenance' => 'Bakımda',
                'scrapped'    => 'Hurda',
                default       => $tool->status,
            };

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $tool->name);
            $sheet->setCellValue('C' . $row, $tool->category_label ?? '—');
            $sheet->setCellValue('D' . $row, $tool->serial_no ?? '—');
            $sheet->setCellValue('E' . $row, $tool->barcode ?? '—');
            $sheet->setCellValue('F' . $row, $tool->location_label ?? '—');
            $sheet->setCellValue('G' . $row, $statusLabel);
            $sheet->setCellValue('H' . $row, $tool->activeLoan?->personnel?->name ?? '—');
            $sheet->setCellValue('I' . $row, $tool->activeLoan?->personnel?->badge_number ?? '—');
            $sheet->setCellValue('J' . $row, $tool->max_loan_days ?? 7);
            $sheet->setCellValue('K' . $row, $tool->created_at?->format('d.m.Y H:i') ?? '—');
            $sheet->setCellValue('L' . $row, $tool->description ?? '');

            // Row style
            $rowStyle = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray($rowStyle);

            if ($tool->status === 'loaned') {
                $sheet->getStyle("A{$row}:L{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFEF3C7'); // Soft Yellow
            } elseif ($tool->status === 'maintenance') {
                $sheet->getStyle("A{$row}:L{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0F2FE'); // Soft Blue
            } elseif ($tool->status === 'scrapped') {
                $sheet->getStyle("A{$row}:L{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFEE2E2'); // Soft Red
            } elseif ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:L{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    // -------------------------------------------------------------------------
    // Parça Listesi (Envanter) A4 Yatay PDF Export
    // -------------------------------------------------------------------------
    public function generateToolsPdf($tools = null): \Barryvdh\DomPDF\PDF
    {
        if ($tools === null) {
            $tools = Tool::query()
                ->with(['slot.shelf.block', 'activeLoan.personnel'])
                ->orderBy('name')
                ->get();
        }

        $stats = [
            'total'       => $tools->count(),
            'available'   => $tools->where('status', 'available')->count(),
            'loaned'      => $tools->where('status', 'loaned')->count(),
            'maintenance' => $tools->where('status', 'maintenance')->count(),
            'scrapped'    => $tools->where('status', 'scrapped')->count(),
        ];

        return Pdf::loadView('reports.tools_inventory_pdf', compact('tools', 'stats'))
            ->setPaper('a4', 'landscape');
    }
}

