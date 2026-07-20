<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_ADMIN             = 'admin';
    const ROLE_INVENTORY_MANAGER = 'inventory_manager';
    const ROLE_SALES             = 'sales';
    const ROLE_PROCUREMENT       = 'procurement';
    const ROLE_FINANCE           = 'finance';
    const ROLE_WAREHOUSE         = 'warehouse';
    const ROLE_AUDITOR           = 'auditor';

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN             => 'Administrator',
            self::ROLE_INVENTORY_MANAGER => 'Inventory Manager',
            self::ROLE_SALES             => 'Sales User',
            self::ROLE_PROCUREMENT       => 'Procurement Officer',
            self::ROLE_FINANCE           => 'Finance User',
            self::ROLE_WAREHOUSE         => 'Warehouse Operator',
            self::ROLE_AUDITOR           => 'Auditor (read-only)',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_changed_at',
        'user_type',
        'role',
        'is_active',
        'google_id',
        'google_token',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'is_active'           => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }


public function profilePhotoUrl(): ?string
{
    return $this->profile_photo_path
        ? Storage::disk('public')->url($this->profile_photo_path)
        : null;
}

public function isAdmin(): bool
{
    return $this->role === self::ROLE_ADMIN || in_array((int) $this->user_type, [1, 2]);
}

public function hasRole(string|array $roles): bool
{
    if ($this->isAdmin()) {
        return true;
    }

    return in_array($this->role, (array) $roles, true);
}

public function isAuditor(): bool
{
    return $this->role === self::ROLE_AUDITOR;
}

public function roleLabel(): string
{
    return self::roles()[$this->role] ?? ucfirst($this->role ?? '—');
}

public function isPasswordExpired(): bool
{
    if (!$this->password_changed_at) {
        return false;
    }

    return $this->password_changed_at->lt(now()->subDays(90));
}

public function expenses()
{
    return $this->hasMany(Expense::class);
}
}
