<?php

use App\Application\Backup\RestoreDatabaseBackupUseCase;
use App\Domain\Backup\BackupFileCipher;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;

uses(Tests\TestCase::class);

it('rejects a sql file during restore', function () {
    $repository = Mockery::mock(DatabaseBackupRepository::class);
    $repository->shouldReceive('restoreFromSqlFile')->never();

    $cipher = Mockery::mock(BackupFileCipher::class);
    $cipher->shouldReceive('isEncryptedFile')->never();

    $useCase = new RestoreDatabaseBackupUseCase($repository, $cipher);
    $useCase->execute('D:\\backups\\backup.sql');
})->throws(RuntimeException::class, 'يجب اختيار ملف نسخة احتياطية مشفّرة بصيغة .esbak.');

it('decrypts an esbak backup before restoring sql', function () {
    $encryptedPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('backup_', true).'.esbak';
    file_put_contents($encryptedPath, 'ESBK-encrypted');

    $repository = Mockery::mock(DatabaseBackupRepository::class);
    $repository->shouldReceive('restoreFromSqlFile')
        ->once()
        ->with(Mockery::on(function (string $path): bool {
            return str_ends_with($path, '.sql') && is_file($path);
        }));

    $cipher = Mockery::mock(BackupFileCipher::class);
    $cipher->shouldReceive('isEncryptedFile')->once()->with($encryptedPath)->andReturn(true);
    $cipher->shouldReceive('decryptFile')
        ->once()
        ->andReturnUsing(function (string $from, string $to): void {
            file_put_contents($to, "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n");
        });

    $useCase = new RestoreDatabaseBackupUseCase($repository, $cipher);
    $useCase->execute($encryptedPath);

    @unlink($encryptedPath);
});
