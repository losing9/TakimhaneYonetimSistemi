<?php

namespace App\Filament\Pages;

use App\Models\DailyReport;
use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Tool;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Raporlar & Arşiv';
    protected static ?string $navigationGroup = 'Zimmet İşlemleri';
    protected static ?string $title           = '📊 Raporlar & Arşiv Yönetimi';
    protected static ?int    $navigationSort  = 2;

    protected static string $view = 'filament.pages.reports';

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    // Filtre state'leri
    public string $activeTab   = 'daily';
    public ?string $fromDate   = null;
    public ?string $toDate     = null;
    public ?int    $personnelId = null;
    public ?int    $toolId      = null;
    public ?int    $archiveYear = null;

    public function mount(): void
    {
        $this->fromDate   = now()->startOfMonth()->toDateString();
        $this->toDate     = now()->toDateString();
        $this->archiveYear = now()->year;
    }

    // -------------------------------------------------------------------------
    // Query Metodları
    // -------------------------------------------------------------------------

    public function getDailyLoans()
    {
        return Loan::query()
            ->with(['tool', 'personnel'])
            ->when($this->fromDate, fn ($q) => $q->whereDate('loaned_at', '>=', $this->fromDate))
            ->when($this->toDate,   fn ($q) => $q->whereDate('loaned_at', '<=', $this->toDate))
            ->orderBy('loaned_at', 'desc')
            ->get();
    }

    public function getOverdueLoans()
    {
        return Loan::overdue()
            ->with(['tool', 'personnel'])
            ->orderBy('planned_return_at')
            ->get();
    }

    public function getPersonnelLoans()
    {
        if (! $this->personnelId) return collect();

        return Loan::query()
            ->with(['tool'])
            ->where('personnel_id', $this->personnelId)
            ->orderBy('loaned_at', 'desc')
            ->get();
    }

    public function getToolHistory()
    {
        if (! $this->toolId) return collect();

        return Loan::query()
            ->with(['personnel'])
            ->where('tool_id', $this->toolId)
            ->orderBy('loaned_at', 'desc')
            ->get();
    }

    public function getArchiveReports()
    {
        return DailyReport::query()
            ->whereYear('report_date', $this->archiveYear ?? now()->year)
            ->orderBy('report_date', 'desc')
            ->get();
    }

    public function getAvailableYears(): array
    {
        $minYear = (int) (Loan::selectRaw('MIN(YEAR(loaned_at)) as min_year')->value('min_year') ?? now()->year);
        $years   = [];
        for ($y = now()->year; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }
        return $years;
    }

    // -------------------------------------------------------------------------
    // İstatistik Metodları
    // -------------------------------------------------------------------------

    public function getDailyStats(): array
    {
        $loans = $this->getDailyLoans();
        return [
            'total'    => $loans->count(),
            'returned' => $loans->where('status', 'returned')->count(),
            'active'   => $loans->where('status', 'active')->count(),
            'overdue'  => $loans->where('status', 'overdue')->count(),
        ];
    }

    public function getToolStats(): array
    {
        if (! $this->toolId) return [];
        $loans = $this->getToolHistory();
        return [
            'total_loans'   => $loans->count(),
            'total_days'    => $loans->sum(fn ($l) => $l->loaned_at->diffInDays($l->returned_at ?? now())),
            'overdue_count' => $loans->where('status', 'overdue')->count(),
        ];
    }

    // -------------------------------------------------------------------------
    // Export Action'ları
    // -------------------------------------------------------------------------

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('📄 PDF İndir')
                ->color('danger')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('export.report', [
                    'from'   => $this->fromDate,
                    'to'     => $this->toDate,
                    'format' => 'pdf',
                    'title'  => 'Zimmet Raporu',
                ]))
                ->openUrlInNewTab(),

            Action::make('export_xlsx')
                ->label('📊 Excel İndir')
                ->color('success')
                ->icon('heroicon-o-table-cells')
                ->url(fn () => route('export.report', [
                    'from'   => $this->fromDate,
                    'to'     => $this->toDate,
                    'format' => 'xlsx',
                    'title'  => 'Zimmet Raporu',
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function applyFilter(): void
    {
        // Livewire reactive - sayfa otomatik güncellenir
    }
}
