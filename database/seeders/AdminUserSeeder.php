<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's default admin account.
     */
    public function run(): void
    {
        $plainPassword = 'admin123';
        $exists = DB::table('users')->where('username', 'admin')->exists();
        if ($exists) {
            $payload = [
                'is_admin' => true,
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'password_display')) {
                $payload['password'] = Hash::make($plainPassword);
                $payload['password_display'] = Crypt::encryptString($plainPassword);
            }

            DB::table('users')->where('username', 'admin')->update($payload);

            return;
        }

        $attributes = [
            'name' => 'المسؤول',
            'username' => 'admin',
            'email' => null,
            'password' => Hash::make($plainPassword),
            'is_admin' => true,
        ];

        if (Schema::hasColumn('users', 'password_display')) {
            $attributes['password_display'] = Crypt::encryptString($plainPassword);
        }

        User::query()->create($attributes);
    }
}
