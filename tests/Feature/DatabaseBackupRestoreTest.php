<?php

use App\Domain\Auth\PermissionCatalog;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('places the database import toolbar control next to backup', function () {
    $html = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $backupPos = strpos($html, 'database-backup.store');
    $restorePos = strpos($html, 'database-backup.restore');

    expect($html)->toContain('استيراد قاعدة بيانات')
        ->and($html)->toContain('database-restore-form')
        ->and($backupPos)->not->toBeFalse()
        ->and($restorePos)->not->toBeFalse()
        ->and($restorePos)->toBeGreaterThan($backupPos);
});

it('shows the database import control on the dashboard toolbar', function () {
    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('استيراد قاعدة بيانات')
        ->assertSee('نسخ احتياطي');
});

it('imports a sql backup file into the current database', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')
        ->once()
        ->with(Mockery::on(fn (string $path): bool => is_file($path)));
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $file = UploadedFile::fake()->createWithContent(
        'backup_exterior_student.sql',
        "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n"
    );

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'), ['file' => $file])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', 'تم استيراد قاعدة البيانات بنجاح.');
});

it('requires a sql file before restoring the database', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')->never();
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('app_dialog');
});

it('rejects a non sql file when restoring the database', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')->never();
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $file = UploadedFile::fake()->create('backup.txt', 10, 'text/plain');

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'), ['file' => $file])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('app_dialog');
});

it('forbids staff without backup permission from restoring the database', function () {
    $staff = new User;
    $staff->forceFill([
        'id' => 2,
        'name' => 'موظف',
        'username' => 'staff',
        'is_admin' => false,
    ]);
    $staff->exists = true;
    $staff->setRelation('permissionRecords', collect());
    $this->actingAs($staff);

    expect($staff->hasPermission(PermissionCatalog::BACKUP_CREATE))->toBeFalse();

    $file = UploadedFile::fake()->createWithContent(
        'backup_exterior_student.sql',
        "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n"
    );

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'), ['file' => $file])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('app_dialog');
});
