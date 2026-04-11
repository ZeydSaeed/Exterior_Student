<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Backup\Repositories\DatabaseBackupRepository;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Process\Process;

final class MySQLDatabaseBackupRepository implements DatabaseBackupRepository
{
    /**
     * {@inheritDoc}
     *
     * @return array{file_path:string,file_name:string,size_bytes:int}
     */
    public function createBackup(string $destinationDir): array
    {
        $mysql = $this->mysqlConnectionConfig();
        if ($mysql === null) {
            throw new RuntimeException('تعذر تحديد إعدادات MySQL لعمل النسخ الاحتياطي.');
        }

        $host = (string) ($mysql['host'] ?? '127.0.0.1');
        $port = (string) ($mysql['port'] ?? '3306');
        $database = (string) ($mysql['database'] ?? '');
        $username = (string) ($mysql['username'] ?? 'root');
        $password = (string) ($mysql['password'] ?? '');
        $charset = (string) ($mysql['charset'] ?? 'utf8mb4');

        if (trim($database) === '') {
            throw new RuntimeException('اسم قاعدة البيانات غير محدد في إعدادات الاتصال.');
        }

        $this->ensureDirectoryExists($destinationDir);

        $timestamp = (new DateTimeImmutable)->format('Y-m-d_H-i-s');
        $fileName = "backup_{$database}_{$timestamp}.sql";
        $filePath = rtrim($destinationDir, '\\/').DIRECTORY_SEPARATOR.$fileName;

        $cmd = [
            'mysqldump',
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            "--default-character-set={$charset}",
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--databases',
            $database,
            '--result-file='.$filePath,
        ];

        if ($password !== '') {
            $cmd[] = "--password={$password}";
        }

        $process = new Process($cmd);
        $process->setTimeout(3600);

        $process->run();
        if (! $process->isSuccessful()) {
            $error = trim((string) $process->getErrorOutput());
            $error = $error !== '' ? $error : 'تعذر تنفيذ mysqldump.';
            throw new RuntimeException($error);
        }

        $sizeBytes = file_exists($filePath) ? (int) (filesize($filePath) ?: 0) : 0;

        return [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'size_bytes' => $sizeBytes,
        ];
    }

    private function ensureDirectoryExists(string $destinationDir): void
    {
        $destinationDir = rtrim($destinationDir, '\\/');
        if ($destinationDir === '') {
            throw new RuntimeException('مسار وجهة النسخ الاحتياطي غير صالح.');
        }

        if (! is_dir($destinationDir)) {
            if (! @mkdir($destinationDir, 0775, true) && ! is_dir($destinationDir)) {
                throw new RuntimeException('تعذر إنشاء مجلد النسخ الاحتياطي: '.$destinationDir);
            }
        }
    }

    /**
     * @return array<string,mixed>|null
     */
    private function mysqlConnectionConfig(): ?array
    {
        $defaultName = (string) config('database.default', 'mysql');
        $default = config("database.connections.{$defaultName}");
        if (is_array($default) && (($default['driver'] ?? '') === 'mysql')) {
            return $default;
        }

        $mysql = config('database.connections.mysql');
        if (is_array($mysql) && (($mysql['driver'] ?? '') === 'mysql')) {
            return $mysql;
        }

        return null;
    }
}
