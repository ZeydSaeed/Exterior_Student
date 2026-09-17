<?php

namespace App\Application\Backup;

use App\Domain\Backup\BackupFileCipher;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;
use RuntimeException;

final class CreateDatabaseBackupUseCase
{
    public function __construct(
        private DatabaseBackupRepository $backupRepository,
        private BackupFileCipher $cipher,
    ) {}

    /**
     * @return array{file_path:string,file_name:string,size_bytes:int}
     */
    public function execute(?string $destinationPath = null): array
    {
        $destinationDir = storage_path('app/database-backup');
        $result = $this->backupRepository->createBackup($destinationDir);
        $plainPath = $result['file_path'];
        $encryptedName = pathinfo($result['file_name'], PATHINFO_FILENAME).'.'.$this->encryptedExtension();
        $encryptedPath = dirname($plainPath).DIRECTORY_SEPARATOR.$encryptedName;

        try {
            $this->cipher->encryptFile($plainPath, $encryptedPath);
        } finally {
            if (is_file($plainPath)) {
                @unlink($plainPath);
            }
        }

        if (! is_file($encryptedPath)) {
            throw new RuntimeException('تعذر تشفير ملف النسخ الاحتياطي.');
        }

        $saved = [
            'file_path' => $encryptedPath,
            'file_name' => $encryptedName,
            'size_bytes' => (int) (filesize($encryptedPath) ?: 0),
        ];

        if (is_string($destinationPath) && trim($destinationPath) !== '') {
            $saved = $this->placeAtDestination($saved['file_path'], $destinationPath);
        }

        return $saved;
    }

    /**
     * @return array{file_path:string,file_name:string,size_bytes:int}
     */
    private function placeAtDestination(string $sourcePath, string $destinationPath): array
    {
        $destinationPath = $this->normalizeDestination($destinationPath);
        $directory = dirname($destinationPath);

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('تعذر إنشاء مجلد الحفظ المختار.');
        }

        if (! @copy($sourcePath, $destinationPath)) {
            throw new RuntimeException('تعذر حفظ الملف في المسار المختار.');
        }

        @unlink($sourcePath);

        if (! is_file($destinationPath)) {
            throw new RuntimeException('تعذر حفظ الملف في المسار المختار.');
        }

        return [
            'file_path' => $destinationPath,
            'file_name' => basename($destinationPath),
            'size_bytes' => (int) (filesize($destinationPath) ?: 0),
        ];
    }

    private function normalizeDestination(string $path): string
    {
        $path = trim(str_replace("\0", '', $path));

        if (! preg_match('/^[A-Za-z]:[\\\\\/]/', $path) && ! str_starts_with($path, '\\\\')) {
            throw new RuntimeException('مسار الحفظ غير صالح.');
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === '') {
            $path .= '.'.$this->encryptedExtension();
        }

        return $path;
    }

    private function encryptedExtension(): string
    {
        $extension = trim((string) config('backup.file_extension', 'esbak'), '.');

        return $extension !== '' ? $extension : 'esbak';
    }
}
