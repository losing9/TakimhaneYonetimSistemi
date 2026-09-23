<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateDailyReport extends Command
{
    protected $signature   = 'reports:generate-daily {date? : Tarih (Y-m-d formatında, varsayılan: dün)}';
    protected $description = 'Günlük raporu oluştur ve arşivle (Excel + PDF)';

    public function handle(ReportService $reportService): int
    {
        $dateArg = $this->argument('date');
        $date    = $dateArg ? Carbon::parse($dateArg) : now()->subDay();

        $this->info("📊 {$date->format('d.m.Y')} tarihli rapor oluşturuluyor...");

        try {
            $report = $reportService->archiveDay($date);

            $this->info("✅ Rapor oluşturuldu!");
            $this->table(
                ['İstatistik', 'Değer'],
                [
                    ['Toplam Zimmet',   $report->total_loans],
                    ['İade Edilen',     $report->total_returns],
                    ['Gecikmiş',        $report->overdue_count],
                    ['Yeni Parça',      $report->new_tools],
                    ['Excel Dosyası',   $report->file_path_xlsx ?? '—'],
                    ['PDF Dosyası',     $report->file_path_pdf  ?? '—'],
                ]
            );

        } catch (\Exception $e) {
            $this->error("❌ Hata: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
