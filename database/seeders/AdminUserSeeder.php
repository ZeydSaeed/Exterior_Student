<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's default admin account.
     */
    public function run(): void
    {
        $exists = DB::table('users')->where('username', 'admin')->exists();
        if ($exists) {
            DB::table('users')->where('username', 'admin')->update([
                'is_admin' => true,
                'updated_at' => now(),
            ]);

            return;
        }

        User::query()->create([
            'name' => 'المسؤول',
            'username' => 'admin',
            'email' => null,
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);
    }
}
