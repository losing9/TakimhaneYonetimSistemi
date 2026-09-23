<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = ['toolroom_id', 'name', 'description'];

    public function toolroom(): BelongsTo
    {
        return $this->belongsTo(Toolroom::class);
    }

    /** @return HasMany<Shelf> */
    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class);
    }

    /**
     * Toplam göz sayısı (tüm raflardaki gözler)
     */
    public function getSlotsCountAttribute(): int
    {
        return $this->shelves()->withCount('slots')->get()->sum('slots_count');
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
