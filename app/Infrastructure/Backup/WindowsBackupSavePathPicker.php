<?php

namespace App\Infrastructure\Backup;

use App\Application\Backup\BackupSavePathPicker;
use RuntimeException;
use Symfony\Component\Process\Process;

final class WindowsBackupSavePathPicker implements BackupSavePathPicker
{
    public function pick(string $suggestedFileName, bool $existingFile = false): ?string
    {
        $exe = base_path('scripts/app-host/BackupPathPicker.exe');
        if (! is_file($exe)) {
            throw new RuntimeException('تعذر فتح نافذة اختيار الملف.');
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'bkpick_');
        if (! is_string($outputPath) || $outputPath === '') {
            throw new RuntimeException('تعذر تجهيز نافذة اختيار الملف.');
        }

        @unlink($outputPath);

        try {
            $process = new Process([
                $exe,
                $outputPath,
                $suggestedFileName,
                $existingFile ? 'open' : 'save',
            ]);
            $process->setTimeout(300);
            $process->run();

            $exitCode = $process->getExitCode();
            if ($exitCode === 2) {
                return null;
            }

            if (! $process->isSuccessful() || ! is_file($outputPath)) {
                throw new RuntimeException('تعذر فتح نافذة اختيار الملف.');
            }

            $path = trim((string) file_get_contents($outputPath));
            if ($path === '') {
                return null;
            }

            return $path;
        } finally {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }
        }
    }
}
