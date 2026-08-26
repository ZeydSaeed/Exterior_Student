<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username')->nullable()->after('name');
            $table->boolean('is_admin')->default(false)->after('password');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
        } else {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('email')->nullable()->change();
            });
        }

        $users = DB::table('users')->select(['id', 'email', 'name'])->get();
        foreach ($users as $user) {
            $username = null;
            if (is_string($user->email) && $user->email !== '' && str_contains($user->email, '@')) {
                $username = strtolower((string) strstr($user->email, '@', true));
            }
            if ($username === null || $username === '') {
                $username = 'user'.$user->id;
            }

            $base = $username;
            $suffix = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base.$suffix;
                $suffix++;
            }

            DB::table('users')->where('id', $user->id)->update([
                'username' => $username,
            ]);
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY username VARCHAR(255) NOT NULL');
        } else {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('username')->nullable(false)->change();
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'is_admin']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
        } else {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('email')->nullable(false)->change();
            });
        }
    }
};
