<?php

namespace App\Filament\Pages;

use App\Models\Loan;
use App\Models\Slot;
use App\Models\Tool;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class QuickReturn extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Hızlı İade';
    protected static ?string $navigationGroup = 'Zimmet İşlemleri';
    protected static ?string $title           = '↩️ Hızlı İade — QR / Seri No ile';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.pages.quick-return';

    // ── State ────────────────────────────────────────────────────────────────

    public string  $scanInput      = '';
    public ?Loan   $foundLoan      = null;
    public ?int    $returnSlotId   = null;
    public string  $notes          = '';
    public ?string $errorMessage   = null;
    public ?string $successMessage = null;

    // ── Tarama ────────────────────────────────────────────────────────────────

    public function searchLoan(): void
    {
        $this->resetMessages();
        $q = trim($this->scanInput);

        if (empty($q)) {
            $this->foundLoan = null;
            return;
        }

        $tool = Tool::where('serial_no', $q)->orWhere('barcode', $q)->first();

        if (! $tool) {
            $this->foundLoan    = null;
            $this->errorMessage = "❌ \"$q\" seri nolu / barkodlu parça bulunamadı.";
            return;
        }

        $loan = $tool->activeLoan()->with(['personnel', 'tool'])->first();

        if (! $loan) {
            $this->foundLoan    = null;
            $this->errorMessage = "ℹ️ Bu parçanın aktif zimmeti yok — zaten takımhanede.";
            return;
        }

        $this->foundLoan = $loan;

        // Önerilen konum: parçanın eski konumu (slot_id yoksa null)
        $this->returnSlotId = $tool->slot_id ?? null;
    }

    public function returnLoan(): void
    {
        $this->resetMessages();

        if (! $this->foundLoan) {
            $this->errorMessage = 'Önce bir parça tarayın.';
            return;
        }

        $loan = $this->foundLoan;
        $tool = $loan->tool;

        $loan->update([
            'returned_at'      => now(),
            'returned_slot_id' => $this->returnSlotId ?: null,
            'status'           => 'returned',
            'notes'            => $loan->notes
                ? $loan->notes . ' | İade notu: ' . $this->notes
                : ($this->notes ?: null),
        ]);

        // Parça durumunu güncelle
        $tool->update([
            'status'  => 'available',
            'slot_id' => $this->returnSlotId ?: $tool->slot_id,
        ]);

        $personnel = $loan->personnel;
        $this->successMessage = "✅ <strong>{$tool->name}</strong> [{$tool->serial_no}] iade alındı. "
            . "<strong>{$personnel->name}</strong>'ın zimmet kaydı kapatıldı.";

        Notification::make()
            ->title('İade alındı!')
            ->body("{$tool->name} — {$personnel->name}")
            ->success()
            ->send();

        $this->reset(['scanInput', 'foundLoan', 'returnSlotId', 'notes']);
    }

    public function clearSearch(): void
    {
        $this->reset(['scanInput', 'foundLoan', 'errorMessage', 'successMessage', 'returnSlotId', 'notes']);
    }

    private function resetMessages(): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;
    }

    public function getSlotListProperty()
    {
        return Slot::with('shelf.block')->get();
    }
}
