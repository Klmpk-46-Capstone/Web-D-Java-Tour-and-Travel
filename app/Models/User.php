<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'whatsapp',
    'email',
    'password',
    'role',
    'is_blacklisted',
    'blacklist_reason',
    'blacklisted_at',
    'last_login_at',
    'last_seen_at',
    'profile_photo_path',
    'identity_number',
    'whatsapp_notifications_enabled',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Konversi tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blacklisted' => 'boolean',
            'blacklisted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'whatsapp_notifications_enabled' => 'boolean',
        ];
    }

    public function isOnline(): bool
    {
        if (! $this->last_seen_at && ! $this->last_login_at) {
            return false;
        }

        $latest = $this->last_seen_at ?? $this->last_login_at;

        return $latest->gt(now()->subMinutes(15));
    }

    public function getActivityStatusBadgeAttribute(): array
    {
        if ($this->is_blacklisted) {
            return [
                'status' => 'blacklisted',
                'label' => 'Blacklisted (Diblokir)',
                'badge_class' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                'dot_class' => 'bg-rose-500',
            ];
        }

        if ($this->isOnline()) {
            return [
                'status' => 'online',
                'label' => 'Online Sekarang',
                'badge_class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                'dot_class' => 'bg-emerald-500 animate-pulse',
            ];
        }

        if ($this->last_login_at) {
            return [
                'status' => 'offline',
                'label' => 'Terakhir ' . $this->last_login_at->diffForHumans(),
                'badge_class' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
                'dot_class' => 'bg-slate-400',
            ];
        }

        return [
            'status' => 'never',
            'label' => 'Belum Pernah Login',
            'badge_class' => 'bg-slate-100 text-slate-500 ring-slate-500/15',
            'dot_class' => 'bg-slate-300',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin_ops', 'admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
