<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    protected $fillable = ['shelf_id', 'name', 'capacity'];

    /** @return BelongsTo<Shelf, Slot> */
    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    /** @return HasMany<Tool> — şu an bu gözde duran parçalar */
    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }

    /**
     * "Blok A / Raf 3 / Göz 2" formatında tam breadcrumb
     */
    public function getFullLabelAttribute(): string
    {
        return $this->shelf->block->name
            . ' / ' . $this->shelf->name
            . ' / ' . $this->name;
    }

    /**
     * Göz dolu mu? (kapasite kontrolü)
     */
    public function isFull(): bool
    {
        return $this->tools()->count() >= $this->capacity;
    }

    public function __toString(): string
    {
        return $this->full_label;
    }
}
