<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Auth\AuthAccountCommandRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ كتابة حسابات الدخول على MySQL.
 */
final class MySQLAuthAccountCommandRepository implements AuthAccountCommandRepository
{
    /**
     * @param  list<string>  $permissions
     */
    public function create(
        string $name,
        string $username,
        string $passwordHash,
        bool $isAdmin,
        array $permissions,
    ): int {
        return (int) DB::transaction(function () use ($name, $username, $passwordHash, $isAdmin, $permissions): int {
            $id = (int) DB::table('users')->insertGetId([
                'name' => $name,
                'username' => $username,
                'email' => null,
                'password' => $passwordHash,
                'is_admin' => $isAdmin,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncPermissions($id, $isAdmin ? [] : $permissions);

            return $id;
        });
    }

    /**
     * @param  list<string>  $permissions
     */
    public function update(
        int $id,
        string $name,
        string $username,
        ?string $passwordHash,
        bool $isAdmin,
        array $permissions,
    ): void {
        DB::transaction(function () use ($id, $name, $username, $passwordHash, $isAdmin, $permissions): void {
            $payload = [
                'name' => $name,
                'username' => $username,
                'is_admin' => $isAdmin,
                'updated_at' => now(),
            ];

            if ($passwordHash !== null) {
                $payload['password'] = $passwordHash;
            }

            DB::table('users')->where('id', $id)->update($payload);
            $this->syncPermissions($id, $isAdmin ? [] : $permissions);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            DB::table('user_permissions')->where('user_id', $id)->delete();
            DB::table('users')->where('id', $id)->delete();
        });
    }

    /**
     * @param  list<string>  $permissions
     */
    private function syncPermissions(int $userId, array $permissions): void
    {
        DB::table('user_permissions')->where('user_id', $userId)->delete();

        if ($permissions === []) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($permissions as $permission) {
            $rows[] = [
                'user_id' => $userId,
                'permission' => $permission,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('user_permissions')->insert($rows);
    }
}
