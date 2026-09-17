<?php

namespace App\Application\Backup;

interface BackupSavePathPicker
{
    public function pick(string $suggestedFileName, bool $existingFile = false): ?string;
}
