<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Tool;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ActiveLoansWidget extends Widget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.active-loans-widget';

    public string $search = '';
    public string $date = '';
    public string $statusFilter = 'all'; // 'all', 'active', 'returned', 'overdue'

    // Zimmet verme modal state'leri
    public bool $showAssignModal = false;
    public ?int $assignPersonnelId = null;
    public ?int $assignToolId = null;
    public int $assignDays = 7;
    public ?string $assignNotes = null;

    public function mount(): void
    {
        $this->date = today()->toDateString();
    }

    public function getIsTodayProperty(): bool
    {
        return empty($this->date) || $this->date === today()->toDateString();
    }

    public function getSelectedCarbonDateProperty(): Carbon
    {
        return Carbon::parse($this->date ?: today()->toDateString());
    }

    public function getAvailableToolsProperty(): Collection
    {
        return Tool::available()->with('slot.shelf.block')->orderBy('name')->get();
    }

    public function getPersonnelListProperty(): Collection
    {
        return Personnel::active()->orderBy('name')->get();
    }

    public function getDailyStatsProperty(): array
    {
        $query = Loan::with(['tool', 'personnel', 'loanedByUser']);
        $carbonDate = $this->selectedCarbonDate;

        if ($this->isToday) {
            $loans = $query->where(function ($q) use ($carbonDate) {
                $q->whereIn('status', ['active', 'overdue'])
                  ->orWhereDate('loaned_at', $carbonDate)
                  ->orWhereDate('returned_at', $carbonDate);
            })->get();
        } else {
            $loans = $query->where(function ($q) use ($carbonDate) {
                $q->whereDate('loaned_at', $carbonDate)
                  ->orWhereDate('returned_at', $carbonDate);
            })->get();
        }

        return [
            'total'    => $loans->count(),
            'active'   => $loans->whereIn('status', ['active', 'overdue'])->count(),
            'returned' => $loans->where('status', 'returned')->count(),
            'overdue'  => $loans->where('status', 'overdue')->count(),
        ];
    }

    public function getGroupedLoansProperty(): Collection
    {
        $query = Loan::with(['tool.slot.shelf.block', 'personnel', 'loanedByUser']);
        $carbonDate = $this->selectedCarbonDate;

        // Hem anlık dışarıda olanları hem de seçilen günde verilen veya iade edilen tüm hareketleri getir
        if ($this->isToday) {
            $loans = $query->where(function ($q) use ($carbonDate) {
                $q->whereIn('status', ['active', 'overdue'])
                  ->orWhereDate('loaned_at', $carbonDate)
                  ->orWhereDate('returned_at', $carbonDate);
            })->orderByDesc('loaned_at')->get();
        } else {
            $loans = $query->where(function ($q) use ($carbonDate) {
                $q->whereDate('loaned_at', $carbonDate)
                  ->orWhereDate('returned_at', $carbonDate);
            })->orderByDesc('loaned_at')->get();
        }

        // Durum Filtresi (all, active, returned, overdue)
        if ($this->statusFilter === 'active') {
            $loans = $loans->whereIn('status', ['active', 'overdue']);
        } elseif ($this->statusFilter === 'returned') {
            $loans = $loans->where('status', 'returned');
        } elseif ($this->statusFilter === 'overdue') {
            $loans = $loans->filter(fn($l) => $l->status === 'overdue' || $l->isOverdue());
        }

        // Personel / Kullanıcı bazında gruplama (Aynı kişi listede sadece 1 kez görünür)
        $grouped = $loans->groupBy(function ($loan) {
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

        if (!empty($this->search)) {
            $term = mb_strtolower($this->search, 'UTF-8');
            $grouped = $grouped->filter(function ($item) use ($term) {
                $pName = mb_strtolower($item['personnel_name'], 'UTF-8');
                $pDept = mb_strtolower($item['personnel_dept'], 'UTF-8');
                $pBadge = mb_strtolower($item['badge_number'], 'UTF-8');
                $toolNames = mb_strtolower($item['loans']->pluck('tool.name')->join(' '), 'UTF-8');
                $serials = mb_strtolower($item['loans']->pluck('tool.serial_no')->join(' '), 'UTF-8');

                return str_contains($pName, $term)
                    || str_contains($pDept, $term)
                    || str_contains($pBadge, $term)
                    || str_contains($toolNames, $term)
                    || str_contains($serials, $term);
            });
        }

        return $grouped;
    }

    public function resetDateToToday(): void
    {
        $this->date = today()->toDateString();
    }

    public function openAssignModal(?int $personnelId = null): void
    {
        $this->assignPersonnelId = $personnelId;
        $this->assignToolId = null;
        $this->assignDays = 7;
        $this->assignNotes = null;
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
    }

    /**
     * Zaman damgalı parça zimmetle
     */
    public function assignLoan(): void
    {
        if (!$this->assignPersonnelId || !$this->assignToolId) {
            Notification::make()
                ->title('Hata')
                ->body('Lütfen hem personel hem de parça seçiniz.')
                ->danger()
                ->send();
            return;
        }

        $tool = Tool::find($this->assignToolId);

        if (!$tool || $tool->status === 'loaned') {
            Notification::make()
                ->title('Hata')
                ->body('Seçilen parça müsait değil veya başka bir zimmette.')
                ->danger()
                ->send();
            return;
        }

        $personnel = Personnel::find($this->assignPersonnelId);
        $now = now(); // Saniyesine kadar hassas zaman damgası
        $plannedReturn = $now->copy()->addDays(max(1, $this->assignDays));

        Loan::create([
            'tool_id'           => $tool->id,
            'personnel_id'      => $personnel->id,
            'loaned_at'         => $now,
            'planned_return_at' => $plannedReturn,
            'status'            => 'active',
            'notes'             => $this->assignNotes,
            'loaned_by_user_id' => Auth::id(),
            'created_by'        => Auth::id(),
        ]);

        $tool->update(['status' => 'loaned']);

        $this->showAssignModal = false;

        Notification::make()
            ->title('Zimmet Kaydedildi')
            ->body("✅ '{$tool->name}' parçası {$now->format('d.m.Y H:i:s')} zaman damgası ile '{$personnel->name}' personeline zimmetlendi.")
            ->success()
            ->send();
    }

    /**
     * Tekil aleti iade al
     */
    public function returnSingleLoan(int $loanId): void
    {
        $loan = Loan::with('tool', 'personnel')->find($loanId);

        if (!$loan || $loan->status === 'returned') {
            Notification::make()
                ->title('Hata')
                ->body('Bu zimmet kaydı bulunamadı veya zaten iade edilmiş.')
                ->danger()
                ->send();
            return;
        }

        $toolName = $loan->tool?->name ?? 'Alet';
        $personnelName = $loan->personnel?->name ?? 'Personel';

        $loan->update([
            'returned_at'            => now(),
            'status'                 => 'returned',
            'return_condition_notes' => 'Filament Yönetim Paneli üzerinden iade alındı.',
        ]);

        if ($loan->tool) {
            $loan->tool->update(['status' => 'available']);
        }

        Notification::make()
            ->title('İade Alındı')
            ->body("✅ '{$toolName}' ({$personnelName}) başarıyla teslim alındı ve alet boşa çıkarıldı.")
            ->success()
            ->send();
    }

    /**
     * Personelin elindeki tüm aletleri topluca iade al
     */
    public function returnAllLoansForPersonnel(?int $personnelId, ?int $loanedByUserId = null): void
    {
        $query = Loan::with('tool')
            ->whereIn('status', ['active', 'overdue']);

        if ($personnelId) {
            $query->where('personnel_id', $personnelId);
        } elseif ($loanedByUserId) {
            $query->where('loaned_by_user_id', $loanedByUserId);
        } else {
            return;
        }

        $loans = $query->get();

        if ($loans->isEmpty()) {
            return;
        }

        foreach ($loans as $loan) {
            $loan->update([
                'returned_at'            => now(),
                'status'                 => 'returned',
                'return_condition_notes' => 'Filament üzerinden toplu iade alındı.',
            ]);

            if ($loan->tool) {
                $loan->tool->update(['status' => 'available']);
            }
        }

        Notification::make()
            ->title('Toplu İade Başarılı')
            ->body("✅ Toplam {$loans->count()} parça başarıyla iade alındı.")
            ->success()
            ->send();
    }
}
