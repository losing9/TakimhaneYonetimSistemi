<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = [
        'toolroom_id',
        'tool_id',
        'personnel_id',
        'loaned_at',
        'planned_return_at',
        'returned_at',
        'returned_slot_id',
        'status',
        'notes',
        'created_by',
        'return_photo',
        'return_condition_notes',
        'return_station_id',
        'loaned_by_user_id',
    ];

    public function toolroom(): BelongsTo
    {
        return $this->belongsTo(Toolroom::class);
    }

    protected $casts = [
        'loaned_at'         => 'datetime',
        'planned_return_at' => 'datetime',
        'returned_at'       => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // İlişkiler
    // -------------------------------------------------------------------------

    /** @return BelongsTo<Tool, Loan> */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /** @return BelongsTo<Personnel, Loan> */
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    /** @return BelongsTo<Slot, Loan> — iade sırasındaki konum */
    public function returnedSlot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'returned_slot_id');
    }

    /** @return BelongsTo<\App\Models\User, Loan> — işlemi yapan panel kullanıcısı */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /** @return BelongsTo<\App\Models\User, Loan> — self-service portalından teslim alan kullanıcı */
    public function loanedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'loaned_by_user_id');
    }

    /** @return BelongsTo<\App\Models\Station, Loan> — iade edilen istasyon */
    public function returnStation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Station::class, 'return_station_id');
    }

    // -------------------------------------------------------------------------
    // Query Scope'lar
    // -------------------------------------------------------------------------

    /** Sadece aktif (ödünçte) zimmetler */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Sadece gecikmiş zimmetler */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue');
    }

    /** Sadece iade edilmiş zimmetler */
    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('status', 'returned');
    }

    /** Bugün ödünç alınanlar */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('loaned_at', today());
    }

    /** Bugün iade edilmesi gereken ama hâlâ ödünçteki zimmetler */
    public function scopeDueTodayAndNotReturned(Builder $query): Builder
    {
        return $query->whereDate('planned_return_at', '<=', today())
                     ->whereNull('returned_at')
                     ->whereIn('status', ['active', 'overdue']);
    }

    // -------------------------------------------------------------------------
    // Accessor'lar
    // -------------------------------------------------------------------------

    /** Gecikme kaç gün? */
    public function getOverdueDaysAttribute(): int
    {
        if ($this->returned_at || $this->planned_return_at->isFuture()) {
            return 0;
        }
        return (int) $this->planned_return_at->diffInDays(now());
    }

    /** Durum etiketi Türkçe */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Ödünçte',
            'returned' => 'İade Edildi',
            'overdue'  => 'Gecikmede',
            default    => ucfirst($this->status),
        };
    }

    /** Zimmet gecikmiş mi? */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue'
            || (! $this->returned_at && $this->planned_return_at->isPast());
    }
}
