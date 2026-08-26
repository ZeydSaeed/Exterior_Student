<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->app['auth']->guard('web')->forgetUser();

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

it('shows the login page to guests', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('نظام إدارة الطلبة')
        ->assertSee('اسم الدخول')
        ->assertSee('كلمة المرور')
        ->assertDontSee('تذكرني');
});

it('redirects guests from the dashboard to login', function () {
    $this->actingAsGuest()
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('authenticates a valid admin and reaches the dashboard', function () {
    User::factory()->admin()->create([
        'username' => 'admin',
        'password' => Hash::make('admin123'),
    ]);

    $this->post(route('login.store'), [
        'username' => 'admin',
        'password' => 'admin123',
    ])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
});

it('rejects invalid credentials', function () {
    User::factory()->admin()->create([
        'username' => 'admin',
        'password' => Hash::make('admin123'),
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'username' => 'admin',
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('logs the user out', function () {
    $user = User::factory()->admin()->create([
        'username' => 'admin',
        'password' => Hash::make('admin123'),
    ]);

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
