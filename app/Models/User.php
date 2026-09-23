<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kullanıcı rolleri:
     * - super_admin       → Tüm yetkiler
     * - takimhane_sor     → Ödünç verme/alma, parça yönetimi (kendi takımhanesi)
     * - personel          → Salt okunur
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'toolroom_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * FilamentUser: Sadece admin roller panele girebilir.
     * Personel rolü portal'a yönlendirilir.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['super_admin', 'takimhane_sor']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isTakimhaneSorumlusu(): bool
    {
        return in_array($this->role, ['super_admin', 'takimhane_sor']);
    }

    public function isPersonel(): bool
    {
        return $this->role === 'personel';
    }

    /** Portal girişi sonrası yönlendirilecek URL */
    public function portalRedirectUrl(): string
    {
        return $this->isPersonel() ? '/portal' : '/admin';
    }

    /** Bu kullanıcıya bağlı personel kaydı */
    public function personnel(): HasOne
    {
        return $this->hasOne(Personnel::class);
    }

    /** Kullanıcının sorumlu olduğu takımhane (Süper admin için null olabilir) */
    public function toolroom(): BelongsTo
    {
        return $this->belongsTo(Toolroom::class);
    }

    /**
     * Kullanıcının belirtilen takımhaneyi yönetme yetkisi var mı?
     */
    public function canManageToolroom(?int $toolroomId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->toolroom_id !== null && $this->toolroom_id === (int) $toolroomId;
    }
}
