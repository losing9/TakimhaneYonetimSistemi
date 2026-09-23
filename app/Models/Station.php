<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Station extends Model
{
    protected $fillable = [
        'toolroom_id',
        'name',
        'qr_token',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function toolroom(): BelongsTo
    {
        return $this->belongsTo(Toolroom::class);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Station $station) {
            if (empty($station->qr_token)) {
                $station->qr_token = 'STATION-TAKIM-' . strtoupper(Str::random(10));
            }
        });
    }

    public function isValid(): bool
    {
        return $this->is_active;
    }

    /**
     * Bu haftanın dinamik QR token'ını üretir (Haftada bir otomatik değişir)
     * Format: STN:{id}:W{hafta_no}:{kısa_hash} (Örn: STN:1:W38:8F4B2D)
     */
    public function getWeeklyDynamicToken(?\DateTimeInterface $date = null): string
    {
        $date = $date ?? now();
        $weekKey = $date->format('o-W'); // ISO-8601 yıl ve hafta numarası
        $secret = config('app.key', 'takimhane-secret');
        $hash = strtoupper(substr(hash_hmac('sha256', "{$this->id}-{$this->qr_token}-{$weekKey}", $secret), 0, 6));

        return "STN:{$this->id}:W" . $date->format('W') . ":{$hash}";
    }

    /**
     * Gelen QR token'ın geçerli haftaya (veya son 24 saatteki önceki haftaya) ait olup olmadığını doğrular
     */
    public function verifyWeeklyToken(string $token): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $token = trim($token);

        // 1. Bu haftanın token'ı ile eşleşiyor mu?
        if ($token === $this->getWeeklyDynamicToken(now())) {
            return true;
        }

        // 2. Geçiş toleransı: 24 saat önceki (önceki hafta sonu) token geçerli mi?
        if ($token === $this->getWeeklyDynamicToken(now()->subDay())) {
            return true;
        }

        // 3. Sabit qr_token uyumluluğu (Eski/kalıcı kurulumlar için fallback)
        if ($token === $this->qr_token) {
            return true;
        }

        return false;
    }

    /** QR içeriği (ekranda ve çıktıda gösterilecek) */
    public function getQrContentAttribute(): string
    {
        return $this->getWeeklyDynamicToken();
    }
}
