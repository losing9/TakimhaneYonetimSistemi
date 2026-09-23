<?php

namespace App\Filament\Pages;

use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Tool;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class QuickLoan extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Hızlı Zimmet';
    protected static ?string $navigationGroup = 'Zimmet İşlemleri';
    protected static ?string $title           = '⚡ Hızlı Zimmet — QR / Seri No ile';
    protected static ?int    $navigationSort  = 0;   // En üstte

    protected static string $view = 'filament.pages.quick-loan';

    // ── State ────────────────────────────────────────────────────────────────

    public string  $scanInput    = '';
    public ?Tool   $foundTool    = null;
    public ?int    $personnelId  = null;
    public int     $loanDays     = 1;
    public string  $notes        = '';
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    // ── Tarama / Arama ────────────────────────────────────────────────────────

    /**
     * QR tarayıcı ya da klavyeden girilen seri no ile parçayı bul.
     * Enter tuşunda veya Livewire wire:change ile tetiklenir.
     */
    public function searchTool(): void
    {
        $this->resetMessages();
        $q = trim($this->scanInput);

        if (empty($q)) {
            $this->foundTool = null;
            return;
        }

        // Göz (Slot) QR kodu mu okutuldu?
        if (preg_match('/^SLOT:(\d+)/i', $q, $m)) {
            $slotId = (int) $m[1];
            $this->redirect(route('filament.admin.resources.tools.index', ['tableFilters[slot_id][value]' => $slotId]));
            return;
        }

        // Seri no veya barkod üzerinden ara
        $tool = Tool::where('serial_no', $q)
            ->orWhere('barcode', $q)
            ->first();

        if (! $tool) {
            $this->foundTool    = null;
            $this->errorMessage = "❌ \"$q\" seri nolu / barkodlu parça bulunamadı.";
            return;
        }

        if ($tool->status === 'scrapped') {
            $this->foundTool    = null;
            $this->errorMessage = "🗑️ Bu parça hurda olarak işaretlenmiş — zimmet verilemez.";
            return;
        }

        if ($tool->status === 'loaned' || $tool->activeLoan) {
            $borrower = $tool->activeLoan?->personnel?->name ?? 'Bilinmiyor';
            $this->foundTool    = null;
            $this->errorMessage = "📤 Bu parça şu an <strong>{$borrower}</strong> üzerinde kayıtlı. Önce iade edilmeli.";
            return;
        }

        $this->foundTool  = $tool;
        $this->loanDays   = $tool->max_loan_days; // Varsayılanı parçadan al
    }

    /**
     * Zimmet oluştur
     */
    public function createLoan(): void
    {
        $this->resetMessages();

        // Validasyon
        if (! $this->foundTool) {
            $this->errorMessage = 'Önce bir parça tarayın.';
            return;
        }
        if (! $this->personnelId) {
            $this->errorMessage = 'Personel seçmediniz.';
            return;
        }
        if ($this->loanDays < 1) {
            $this->errorMessage = 'Gün sayısı en az 1 olmalı.';
            return;
        }

        $tool      = $this->foundTool;
        $personnel = Personnel::find($this->personnelId);

        // Zimmet oluştur
        Loan::create([
            'tool_id'          => $tool->id,
            'personnel_id'     => $personnel->id,
            'loaned_at'        => now(),
            'planned_return_at'=> now()->addDays($this->loanDays)->endOfDay(),
            'status'           => 'active',
            'notes'            => $this->notes ?: null,
            'created_by'       => auth()->id(),
        ]);

        // Parça durumunu güncelle
        $tool->update(['status' => 'loaned', 'slot_id' => null]);

        $this->successMessage = "✅ <strong>{$tool->name}</strong> [{$tool->serial_no}] → <strong>{$personnel->name}</strong> zimmetlendi. İade tarihi: " . now()->addDays($this->loanDays)->format('d.m.Y');

        Notification::make()
            ->title('Zimmet oluşturuldu!')
            ->body("{$tool->name} — {$personnel->name}")
            ->success()
            ->send();

        // Formu sıfırla (yeni tarama için hazır)
        $this->reset(['scanInput', 'foundTool', 'personnelId', 'loanDays', 'notes']);
    }

    public function clearSearch(): void
    {
        $this->reset(['scanInput', 'foundTool', 'errorMessage', 'successMessage', 'personnelId', 'loanDays', 'notes']);
    }

    private function resetMessages(): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;
    }

    public function getPersonnelListProperty()
    {
        return Personnel::active()->orderBy('name')->get();
    }
}
