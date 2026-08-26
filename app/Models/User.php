<?php

namespace App\Models;

use App\Domain\Auth\PermissionCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function permissionRecords(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->is_admin) {
            return true;
        }

        if (! PermissionCatalog::isValid($permission)) {
            return false;
        }

        if ($this->relationLoaded('permissionRecords')) {
            return $this->permissionRecords->contains('permission', $permission);
        }

        return DB::table('user_permissions')
            ->where('user_id', $this->id)
            ->where('permission', $permission)
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function permissionKeys(): array
    {
        if ($this->is_admin) {
            return PermissionCatalog::all();
        }

        if ($this->relationLoaded('permissionRecords')) {
            return $this->permissionRecords
                ->pluck('permission')
                ->map(static fn ($p): string => (string) $p)
                ->values()
                ->all();
        }

        return DB::table('user_permissions')
            ->where('user_id', $this->id)
            ->pluck('permission')
            ->map(static fn ($p): string => (string) $p)
            ->values()
            ->all();
    }
}
