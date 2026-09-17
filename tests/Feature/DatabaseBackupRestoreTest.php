<?php

use App\Application\Backup\BackupSavePathPicker;
use App\Domain\Auth\PermissionCatalog;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Process\Process;

it('places the database import toolbar control next to backup', function () {
    $html = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $backupPos = strpos($html, 'database-backup.store');
    $restorePos = strpos($html, 'database-backup.restore');

    $script = (string) file_get_contents(public_path('js/database-backup.js'));
    $restoreScript = (string) file_get_contents(public_path('js/database-restore.js'));

    expect($html)->toContain('استيراد قاعدة بيانات')
        ->and($html)->toContain('database-restore-form')
        ->and($html)->toContain('database-backup-form')
        ->and($html)->toContain('js/database-backup.js')
        ->and($html)->toContain('type="button" id="database-backup-trigger"')
        ->and($html)->toContain('.esbak')
        ->and($html)->toContain('accept=".esbak,application/octet-stream"')
        ->and($html)->not->toContain('accept=".esbak,.sql')
        ->and($script)->toContain('pick_path')
        ->and($script)->toContain('database-backup-trigger')
        ->and($restoreScript)->toContain('pick_path')
        ->and($restoreScript)->toContain('database-restore-trigger')
        ->and((string) file_get_contents(public_path('js/app-error-dialog.js')))
        ->toContain(', function () {')
        ->and((string) file_get_contents(app_path('Infrastructure/Persistence/MySQLDatabaseBackupRepository.php')))
        ->toContain('mysqlBinary')
        ->and((string) file_get_contents(app_path('Infrastructure/Backup/WindowsBackupSavePathPicker.php')))
        ->toContain('webview')
        ->and((string) file_get_contents(base_path('scripts/webview-host/BackupPathPicker.cs')))->toContain('"open"')
        ->and(file_exists(base_path('scripts/app-host/BackupPathPicker.exe')))->toBeTrue()
        ->and((string) file_get_contents(base_path('scripts/webview-host/BackupPathPicker.cs')))->toContain('OpenFileDialog')
        ->and($backupPos)->not->toBeFalse()
        ->and($restorePos)->not->toBeFalse()
        ->and($restorePos)->toBeGreaterThan($backupPos);

    $backupBlock = substr($html, (int) strpos($html, 'id="database-backup-trigger"'), 700);
    $restoreBlock = substr($html, (int) strpos($html, 'id="database-restore-trigger"'), 700);

    expect($backupBlock)->toContain('polyline points="17 8 12 3 7 8"')
        ->and($backupBlock)->toContain('<line x1="12" y1="3" x2="12" y2="15"/>')
        ->and($restoreBlock)->toContain('polyline points="7 10 12 15 17 10"')
        ->and($restoreBlock)->toContain('<path d="M12 15V3"/>');
});

it('restores an encrypted backup from a windows-chosen path without uploading', function () {
    $plain = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('plain_', true).'.sql';
    $encrypted = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('enc_', true).'.esbak';
    file_put_contents($plain, "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n");
    app(\App\Domain\Backup\BackupFileCipher::class)->encryptFile($plain, $encrypted);

    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')
        ->once()
        ->with(Mockery::on(fn (string $path): bool => is_file($path) && str_ends_with($path, '.sql')));
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $picker = Mockery::mock(BackupSavePathPicker::class);
    $picker->shouldReceive('pick')
        ->once()
        ->with('backup.esbak', true)
        ->andReturn($encrypted);
    $this->app->instance(BackupSavePathPicker::class, $picker);

    $this->post(route('database-backup.restore'), ['pick_path' => 1], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json',
    ])
        ->assertSuccessful()
        ->assertJsonFragment([
            'message' => 'تم استيراد قاعدة البيانات بنجاح.',
        ]);

    expect(is_file($encrypted))->toBeTrue();

    @unlink($plain);
    @unlink($encrypted);
});

it('does not restore when the windows restore dialog is cancelled', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')->never();
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $picker = Mockery::mock(BackupSavePathPicker::class);
    $picker->shouldReceive('pick')->once()->with('backup.esbak', true)->andReturn(null);
    $this->app->instance(BackupSavePathPicker::class, $picker);

    $this->post(route('database-backup.restore'), ['pick_path' => 1], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json',
    ])
        ->assertSuccessful()
        ->assertJson(['cancelled' => true]);
});

it('shows the database import control on the dashboard toolbar', function () {
    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('استيراد قاعدة بيانات')
        ->assertSee('نسخ احتياطي')
        ->assertSee('database-backup-form', false)
        ->assertSee('database-backup-trigger', false)
        ->assertSee('js/database-backup.js', false);
});

it('configures chrome to ask where to save the backup file', function () {
    $profile = storage_path('app/testing/chrome-profile-'.uniqid());

    $process = new Process([
        'powershell.exe',
        '-NoProfile',
        '-ExecutionPolicy',
        'Bypass',
        '-File',
        base_path('scripts/ensure-chrome-save-dialog.ps1'),
        '-ProfileRoot',
        $profile,
    ]);
    $process->run();

    expect($process->isSuccessful())->toBeTrue()
        ->and((string) file_get_contents($profile.DIRECTORY_SEPARATOR.'Default'.DIRECTORY_SEPARATOR.'Preferences'))
        ->toContain('"prompt_for_download":true');

    file_put_contents(
        $profile.DIRECTORY_SEPARATOR.'Default'.DIRECTORY_SEPARATOR.'Preferences',
        '{"download":{"prompt_for_download":false},"other":1}'
    );

    $process->run();

    expect($process->isSuccessful())->toBeTrue()
        ->and((string) file_get_contents($profile.DIRECTORY_SEPARATOR.'Default'.DIRECTORY_SEPARATOR.'Preferences'))
        ->toContain('"prompt_for_download":true')
        ->and((string) file_get_contents($profile.DIRECTORY_SEPARATOR.'Default'.DIRECTORY_SEPARATOR.'Preferences'))
        ->toContain('"other":1');
});

it('saves the backup to the windows path chosen by the user', function () {
    $directory = storage_path('app/database-backup');
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $fileName = 'backup_exterior_student_test.sql';
    $filePath = $directory.DIRECTORY_SEPARATOR.$fileName;
    file_put_contents($filePath, "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n");

    $destination = $directory.DIRECTORY_SEPARATOR.'user-chosen.esbak';
    @unlink($destination);

    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('createBackup')
        ->once()
        ->andReturn([
            'file_path' => $filePath,
            'file_name' => $fileName,
            'size_bytes' => (int) filesize($filePath),
        ]);
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $picker = Mockery::mock(BackupSavePathPicker::class);
    $picker->shouldReceive('pick')
        ->once()
        ->andReturn($destination);
    $this->app->instance(BackupSavePathPicker::class, $picker);

    $this->post(route('database-backup.store'), ['pick_path' => 1], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json',
    ])
        ->assertSuccessful()
        ->assertJsonFragment([
            'message' => 'تم حفظ النسخ الاحتياطي في الموقع الذي اخترته.',
        ]);

    expect(is_file($destination))->toBeTrue()
        ->and((string) file_get_contents($destination))->toStartWith('ESBK');

    @unlink($destination);
});

it('does not create a backup when the windows path dialog is cancelled', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('createBackup')->never();
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $picker = Mockery::mock(BackupSavePathPicker::class);
    $picker->shouldReceive('pick')->once()->andReturn(null);
    $this->app->instance(BackupSavePathPicker::class, $picker);

    $this->post(route('database-backup.store'), ['pick_path' => 1], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json',
    ])
        ->assertSuccessful()
        ->assertJson(['cancelled' => true]);
});

it('downloads an encrypted backup file that is not readable as sql', function () {
    $directory = storage_path('app/database-backup');
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $fileName = 'backup_exterior_student_test.sql';
    $filePath = $directory.DIRECTORY_SEPARATOR.$fileName;
    $sql = "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\nINSERT INTO `students` VALUES ('أحمد');\n";
    file_put_contents($filePath, $sql);

    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('createBackup')
        ->once()
        ->andReturn([
            'file_path' => $filePath,
            'file_name' => $fileName,
            'size_bytes' => (int) filesize($filePath),
        ]);
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $response = $this->post(route('database-backup.store'));
    $response->assertSuccessful()
        ->assertDownload('backup_exterior_student_test.esbak')
        ->assertHeader('content-type', 'application/octet-stream');

    $base = $response->baseResponse;
    expect($base)->toBeInstanceOf(Symfony\Component\HttpFoundation\BinaryFileResponse::class);

    $downloaded = (string) file_get_contents($base->getFile()->getPathname());
    expect($downloaded)->not->toContain('CREATE TABLE')
        ->and($downloaded)->not->toContain('أحمد')
        ->and($downloaded)->toStartWith('ESBK');
});

it('returns a json error when ajax backup creation fails', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('createBackup')
        ->once()
        ->andThrow(new RuntimeException('mysqldump failed'));
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $this->post(route('database-backup.store'), [], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json',
    ])
        ->assertUnprocessable()
        ->assertJsonFragment([
            'message' => 'تعذر إنشاء النسخ الاحتياطي: mysqldump failed',
        ]);
});

it('uses an arabic message when a backup file fails to upload', function () {
    $request = new App\Http\Requests\ImportDatabaseBackupRequest;

    expect($request->messages()['file.uploaded'])->toContain('تعذر رفع الملف');
});

it('rejects a sql backup file when restoring the database', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')->never();
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $file = UploadedFile::fake()->createWithContent(
        'backup_exterior_student.sql',
        "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n"
    );

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'), ['file' => $file])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('app_dialog');
});

it('imports an encrypted backup file into the current database', function () {
    $plain = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('plain_', true).'.sql';
    $encrypted = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('enc_', true).'.esbak';
    file_put_contents($plain, "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n");
    app(\App\Domain\Backup\BackupFileCipher::class)->encryptFile($plain, $encrypted);

    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')
        ->once()
        ->with(Mockery::on(fn (string $path): bool => is_file($path) && str_ends_with($path, '.sql')));
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $file = UploadedFile::fake()->createWithContent(
        'backup_exterior_student.esbak',
        (string) file_get_contents($encrypted)
    );

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'), ['file' => $file])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', 'تم استيراد قاعدة البيانات بنجاح.');

    @unlink($plain);
    @unlink($encrypted);
});

it('requires a backup file before restoring the database', function () {
    $repo = Mockery::mock(DatabaseBackupRepository::class);
    $repo->shouldReceive('restoreFromSqlFile')->never();
    $this->app->instance(DatabaseBackupRepository::class, $repo);

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('app_dialog');
});

it('rejects a non esbak file when restoring the database', function () {
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
        'backup_exterior_student.esbak',
        'ESBK'
    );

    $this->from(route('dashboard'))
        ->post(route('database-backup.restore'), ['file' => $file])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('app_dialog');
});
