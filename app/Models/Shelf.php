<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelf extends Model
{
    protected $fillable = ['block_id', 'name', 'description'];

    /** @return BelongsTo<Block, Shelf> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /** @return HasMany<Slot> */
    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    /**
     * "Blok A / Raf 3" formatında tam konum etiketi
     */
    public function getFullLabelAttribute(): string
    {
        return $this->block->name . ' / ' . $this->name;
    }

    public function __toString(): string
    {
        return $this->full_label;
    }
}
