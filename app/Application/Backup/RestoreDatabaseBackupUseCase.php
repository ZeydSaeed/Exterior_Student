<?php

namespace App\Application\Backup;

use App\Domain\Backup\BackupFileCipher;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;
use RuntimeException;

final class RestoreDatabaseBackupUseCase
{
    public function __construct(
        private DatabaseBackupRepository $backupRepository,
        private BackupFileCipher $cipher,
    ) {}

    public function execute(string $backupFilePath): void
    {
        $extension = strtolower((string) pathinfo($backupFilePath, PATHINFO_EXTENSION));
        if ($extension !== 'esbak' || ! $this->cipher->isEncryptedFile($backupFilePath)) {
            throw new RuntimeException('يجب اختيار ملف نسخة احتياطية مشفّرة بصيغة .esbak.');
        }

        $tempPlainPath = dirname($backupFilePath).DIRECTORY_SEPARATOR.uniqid('restore_plain_', true).'.sql';
        $this->cipher->decryptFile($backupFilePath, $tempPlainPath);

        try {
            $this->backupRepository->restoreFromSqlFile($tempPlainPath);
        } finally {
            if (is_file($tempPlainPath)) {
                @unlink($tempPlainPath);
            }
        }
    }
}
