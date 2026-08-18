<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cooperation_id',
        'member_number',
        'name',
        'email',
        'phone',
        'password',
        'address',
        'birth_date',
        'gender',
        'job',
        'profile_photo',
        'join_date',
        'is_active',
        'created_by',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'join_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Roles::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasAnyPermission(array|string $permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();
    }

    public function getAllPermissions(): array
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->toArray();
    }

    public function savingsTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SavingsTransaction::class, 'user_id');
    }

    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }

    public function loans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Loan::class, 'user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();
        
        if ($panelId === 'login') {
            return true;
        }
        
        // Petugas lapangan offline — tidak punya akses panel manapun.
        return match ($panelId) {
            'admin' => $this->hasRole('admin') || $this->hasRole('manager'),
            'anggota' => $this->hasRole('anggota'),
            'kasir' => $this->hasRole('kasir') || $this->hasRole('cashier') || $this->hasRole('bendahara'),
            'spv' => $this->hasRole('spv') || $this->hasRole('kepalayayasan') || $this->hasRole('kepala_yayasan'),
            default => false,
        };
    }
}
