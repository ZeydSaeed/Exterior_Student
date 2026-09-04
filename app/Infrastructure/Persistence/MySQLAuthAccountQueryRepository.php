<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Auth\AuthAccountQueryRepository;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ قراءة حسابات الدخول من MySQL.
 */
final class MySQLAuthAccountQueryRepository implements AuthAccountQueryRepository
{
    public function listAccounts(): array
    {
        $users = DB::table('users')
            ->select(['id', 'name', 'username', 'is_admin', 'password_display'])
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->get();

        $permissionRows = DB::table('user_permissions')
            ->select(['user_id', 'permission'])
            ->get()
            ->groupBy('user_id');

        $accounts = [];
        foreach ($users as $user) {
            $perms = $permissionRows->get($user->id, collect())
                ->pluck('permission')
                ->map(static fn ($p): string => (string) $p)
                ->values()
                ->all();

            $accounts[] = [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
                'username' => (string) $user->username,
                'is_admin' => (bool) $user->is_admin,
                'password_display' => $this->decryptPasswordDisplay($user->password_display ?? null),
                'permissions' => $perms,
            ];
        }

        return $accounts;
    }

    public function findById(int $id): ?array
    {
        $user = DB::table('users')
            ->select(['id', 'name', 'username', 'is_admin', 'password_display'])
            ->where('id', $id)
            ->first();

        if ($user === null) {
            return null;
        }

        $permissions = DB::table('user_permissions')
            ->where('user_id', $id)
            ->pluck('permission')
            ->map(static fn ($p): string => (string) $p)
            ->values()
            ->all();

        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'username' => (string) $user->username,
            'is_admin' => (bool) $user->is_admin,
            'password_display' => $this->decryptPasswordDisplay($user->password_display ?? null),
            'permissions' => $permissions,
        ];
    }

    public function findByUsername(string $username): ?array
    {
        $user = DB::table('users')
            ->select(['id', 'name', 'username', 'password', 'is_admin'])
            ->where('username', $username)
            ->first();

        if ($user === null) {
            return null;
        }

        $permissions = DB::table('user_permissions')
            ->where('user_id', $user->id)
            ->pluck('permission')
            ->map(static fn ($p): string => (string) $p)
            ->values()
            ->all();

        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'username' => (string) $user->username,
            'password' => (string) $user->password,
            'is_admin' => (bool) $user->is_admin,
            'permissions' => $permissions,
        ];
    }

    public function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $query = DB::table('users')->where('username', $username);
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    private function decryptPasswordDisplay(mixed $encrypted): ?string
    {
        if (! is_string($encrypted) || trim($encrypted) === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }
    }
}
