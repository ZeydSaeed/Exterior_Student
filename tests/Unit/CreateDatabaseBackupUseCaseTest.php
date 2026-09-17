<?php

use App\Application\Backup\CreateDatabaseBackupUseCase;
use App\Domain\Backup\BackupFileCipher;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;

uses(Tests\TestCase::class);

it('encrypts the backup file and removes the readable sql dump', function () {
    $plainPath = storage_path('app/database-backup/backup.sql');
    $directory = dirname($plainPath);
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
    file_put_contents($plainPath, "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n");

    $repository = Mockery::mock(DatabaseBackupRepository::class);
    $repository->shouldReceive('createBackup')
        ->once()
        ->with(storage_path('app/database-backup'))
        ->andReturn([
            'file_path' => $plainPath,
            'file_name' => 'backup.sql',
            'size_bytes' => 12,
        ]);

    $cipher = Mockery::mock(BackupFileCipher::class);
    $cipher->shouldReceive('encryptFile')
        ->once()
        ->with($plainPath, Mockery::on(fn (string $path): bool => str_ends_with($path, 'backup.esbak')))
        ->andReturnUsing(function (string $from, string $to) use ($plainPath): void {
            file_put_contents($to, 'ESBK-encrypted');
            @unlink($plainPath);
        });

    $useCase = new CreateDatabaseBackupUseCase($repository, $cipher);
    $result = $useCase->execute();

    expect($result['file_name'])->toBe('backup.esbak')
        ->and(str_ends_with($result['file_path'], 'backup.esbak'))->toBeTrue()
        ->and(is_file($plainPath))->toBeFalse()
        ->and(is_file($result['file_path']))->toBeTrue();

    @unlink($result['file_path']);
});

it('places the encrypted backup at the chosen windows path', function () {
    $plainPath = storage_path('app/database-backup/backup.sql');
    $directory = dirname($plainPath);
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
    file_put_contents($plainPath, "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n");

    $repository = Mockery::mock(DatabaseBackupRepository::class);
    $repository->shouldReceive('createBackup')
        ->once()
        ->with(storage_path('app/database-backup'))
        ->andReturn([
            'file_path' => $plainPath,
            'file_name' => 'backup.sql',
            'size_bytes' => 12,
        ]);

    $cipher = Mockery::mock(BackupFileCipher::class);
    $cipher->shouldReceive('encryptFile')
        ->once()
        ->andReturnUsing(function (string $from, string $to) use ($plainPath): void {
            file_put_contents($to, 'ESBK-encrypted');
            @unlink($plainPath);
        });

    $destination = $directory.DIRECTORY_SEPARATOR.'chosen-location.esbak';
    @unlink($destination);

    $useCase = new CreateDatabaseBackupUseCase($repository, $cipher);
    $result = $useCase->execute($destination);

    expect($result['file_path'])->toBe($destination)
        ->and($result['file_name'])->toBe('chosen-location.esbak')
        ->and(is_file($destination))->toBeTrue()
        ->and(is_file($plainPath))->toBeFalse();

    @unlink($destination);
});
