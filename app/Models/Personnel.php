<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use SoftDeletes;

    protected $table = 'personnel'; // Laravel varsayılanı "personnels" yapar, düzeltiyoruz

    protected $fillable = [
        'name',
        'badge_number',
        'department',
        'email',
        'phone',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // İlişkiler
    // -------------------------------------------------------------------------

    /** @return BelongsTo<User, Personnel> — portal girişi yapan kullanıcı */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Loan> — tüm zimmet geçmişi */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /** @return HasMany<Loan> — şu an üzerindeki aktif zimmetler */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)
                    ->whereIn('status', ['active', 'overdue']);
    }

    // -------------------------------------------------------------------------
    // Query Scope'lar
    // -------------------------------------------------------------------------

    /** Sadece aktif personeller */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Üzerinde gecikmiş parçası olan personeller */
    public function scopeWithOverdue(Builder $query): Builder
    {
        return $query->whereHas('loans', function (Builder $q) {
            $q->where('status', 'overdue');
        });
    }

    // -------------------------------------------------------------------------
    // Accessor'lar
    // -------------------------------------------------------------------------

    /** Üzerindeki aktif zimmet sayısı */
    public function getActiveLoanCountAttribute(): int
    {
        return $this->activeLoans()->count();
    }

    public function __toString(): string
    {
        return $this->name . " ({$this->badge_number})";
    }
}
