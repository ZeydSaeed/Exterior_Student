<?php

use App\Domain\Auth\PermissionCatalog;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('user_permissions');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->unique();
        $table->string('email')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->boolean('is_admin')->default(false);
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('user_permissions', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('permission', 100);
        $table->timestamps();
        $table->unique(['user_id', 'permission']);
    });
});

it('allows an admin to open the accounts page', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'password' => Hash::make('admin123'),
    ]);

    $this->actingAs($admin)
        ->get(route('accounts.index'))
        ->assertSuccessful()
        ->assertSee('الحسابات والصلاحيات')
        ->assertSee('إضافة حساب جديد');
});

it('forbids a staff user without users.manage from accounts page', function () {
    $staff = User::factory()->create([
        'username' => 'staff1',
        'is_admin' => false,
        'password' => Hash::make('secret1'),
    ]);

    $this->actingAs($staff)
        ->get(route('accounts.index'))
        ->assertRedirect()
        ->assertSessionHas('app_dialog');
});

it('creates a staff account with selected permissions', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'password' => Hash::make('admin123'),
    ]);

    $this->actingAs($admin)
        ->post(route('accounts.store'), [
            'name' => 'موظف اختبار',
            'username' => 'staff_test',
            'password' => 'secret12',
            'permissions' => [
                PermissionCatalog::NAV_STUDENTS,
                PermissionCatalog::STUDENTS_GRADES_VIEW,
            ],
        ])
        ->assertRedirect(route('accounts.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'staff_test',
        'is_admin' => 0,
    ]);

    $userId = (int) DB::table('users')->where('username', 'staff_test')->value('id');
    $this->assertDatabaseHas('user_permissions', [
        'user_id' => $userId,
        'permission' => PermissionCatalog::NAV_STUDENTS,
    ]);
    $this->assertDatabaseHas('user_permissions', [
        'user_id' => $userId,
        'permission' => PermissionCatalog::STUDENTS_GRADES_VIEW,
    ]);

    $hash = (string) DB::table('users')->where('id', $userId)->value('password');
    expect(Hash::check('secret12', $hash))->toBeTrue();
});

it('updates username password and permissions for a staff account', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'password' => Hash::make('admin123'),
    ]);

    $staffId = (int) DB::table('users')->insertGetId([
        'name' => 'موظف',
        'username' => 'oldstaff',
        'email' => null,
        'password' => Hash::make('oldpass1'),
        'is_admin' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('user_permissions')->insert([
        'user_id' => $staffId,
        'permission' => PermissionCatalog::NAV_STUDENTS,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->put(route('accounts.update', $staffId), [
            'name' => 'موظف محدّث',
            'username' => 'newstaff',
            'password' => 'newpass1',
            'permissions' => [
                PermissionCatalog::STUDENTS_DELETE,
            ],
        ])
        ->assertRedirect(route('accounts.index'));

    $this->assertDatabaseHas('users', [
        'id' => $staffId,
        'username' => 'newstaff',
        'name' => 'موظف محدّث',
    ]);

    $hash = (string) DB::table('users')->where('id', $staffId)->value('password');
    expect(Hash::check('newpass1', $hash))->toBeTrue();

    $this->assertDatabaseMissing('user_permissions', [
        'user_id' => $staffId,
        'permission' => PermissionCatalog::NAV_STUDENTS,
    ]);
    $this->assertDatabaseHas('user_permissions', [
        'user_id' => $staffId,
        'permission' => PermissionCatalog::STUDENTS_DELETE,
    ]);
});

it('deletes a staff account', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'password' => Hash::make('admin123'),
    ]);

    $staff = User::factory()->create([
        'username' => 'todelete',
        'is_admin' => false,
    ]);

    $this->actingAs($admin)
        ->delete(route('accounts.destroy', $staff->id))
        ->assertRedirect(route('accounts.index'));

    $this->assertDatabaseMissing('users', ['id' => $staff->id]);
});
