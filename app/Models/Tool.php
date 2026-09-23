<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'toolroom_id',
        'tool_group_id',
        'name',
        'serial_no',
        'barcode',
        'status',
        'slot_id',
        'max_loan_days',
        'description',
        'category',
        'image',
    ];

    public function toolroom(): BelongsTo
    {
        return $this->belongsTo(Toolroom::class);
    }

    public function toolGroup(): BelongsTo
    {
        return $this->belongsTo(ToolGroup::class, 'tool_group_id');
    }

    protected $casts = [
        'max_loan_days' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Kategoriler (sabit liste — genişletilebilir)
    // -------------------------------------------------------------------------

    public const CATEGORIES = [
        'el_aleti'     => '🔨 El Aleti',
        'elektrikli'   => '⚡ Elektrikli Alet',
        'olcum'        => '📐 Ölçüm & Test',
        'kesici'       => '✂️ Kesici / Delici',
        'baglanti'     => '🔩 Bağlantı Elemanı',
        'pnomatik'     => '💨 Pnömatik',
        'kaldirma'     => '🏗️ Kaldırma & Taşıma',
        'koruyucu'     => '🦺 Koruyucu Ekipman',
        'diger'        => '📦 Diğer',
    ];

    // -------------------------------------------------------------------------
    // Boot — otomatik seri no ve barkod üretimi
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Tool $tool) {
            // Seri no boşsa otomatik üret: TKM-2026-00042
            if (empty($tool->serial_no)) {
                $tool->serial_no = static::generateSerialNo();
            }

            // Barkod boşsa seri no'yu kullan
            if (empty($tool->barcode)) {
                $tool->barcode = $tool->serial_no;
            }
        });
    }

    /**
     * Seri no üretici: TKM-{YIL}-{5 haneli sıra no}
     * Aynı yılda çakışma olmayacak şekilde MAX+1 kullanır.
     */
    public static function generateSerialNo(): string
    {
        $year   = now()->year;
        $prefix = "TKM-{$year}-";

        // Bu yıla ait en yüksek sıra numarasını bul
        $last = static::withTrashed()
            ->where('serial_no', 'like', $prefix . '%')
            ->orderByDesc('serial_no')
            ->value('serial_no');

        $next = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------------------------
    // İlişkiler
    // -------------------------------------------------------------------------

    /** @return BelongsTo<Slot, Tool> — şu anki fiziksel konum */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    /** @return HasMany<Loan> — tüm zimmet geçmişi */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /** @return HasOne<Loan> — şu an aktif olan tek zimmet */
    public function activeLoan(): HasOne
    {
        return $this->hasOne(Loan::class)
                    ->whereIn('status', ['active', 'overdue'])
                    ->latest();
    }

    // -------------------------------------------------------------------------
    // Query Scope'lar
    // -------------------------------------------------------------------------

    /** Sadece "Mevcut" parçalar */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    /** Gecikmiş parçalar (aktif loan'u olan ve planlanan iade tarihi geçmiş) */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereHas('loans', function (Builder $q) {
            $q->whereIn('status', ['active', 'overdue'])
              ->where('planned_return_at', '<', now());
        });
    }

    /** Belirli bir gözdeki parçalar */
    public function scopeInSlot(Builder $query, int $slotId): Builder
    {
        return $query->where('slot_id', $slotId);
    }

    /** Belirli bir raftaki parçalar */
    public function scopeInShelf(Builder $query, int $shelfId): Builder
    {
        return $query->whereHas('slot', function (Builder $q) use ($shelfId) {
            $q->where('shelf_id', $shelfId);
        });
    }

    /** Belirli bir bloktaki parçalar */
    public function scopeInBlock(Builder $query, int $blockId): Builder
    {
        return $query->whereHas('slot.shelf', function (Builder $q) use ($blockId) {
            $q->where('block_id', $blockId);
        });
    }

    // -------------------------------------------------------------------------
    // Yardımcı accessor'lar
    // -------------------------------------------------------------------------

    /** Durum etiketi Türkçe */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'Mevcut',
            'loaned'      => 'Ödünçte',
            'maintenance' => 'Bakımda',
            'scrapped'    => 'Hurda',
            default       => ucfirst($this->status),
        };
    }

    /** Konum breadcrumb'ı — "Blok A / Raf 3 / Göz 2" ya da "—" */
    public function getLocationLabelAttribute(): string
    {
        return $this->slot?->full_label ?? '—';
    }

    /** Kategori etiketi Türkçe */
    public function getCategoryLabelAttribute(): string
    {
        return static::CATEGORIES[$this->category] ?? '📦 Diğer';
    }

    /** Fotoğraf URL'si (public disk) */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? \Storage::url($this->image) : null;
    }

    public function __toString(): string
    {
        return $this->name . ($this->serial_no ? " [{$this->serial_no}]" : '');
    }
}
