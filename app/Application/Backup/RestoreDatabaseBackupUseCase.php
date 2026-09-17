<?php

namespace App\Application\Backup;

use App\Domain\Backup\Repositories\DatabaseBackupRepository;

final class RestoreDatabaseBackupUseCase
{
    public function __construct(
        private DatabaseBackupRepository $backupRepository,
    ) {}

    public function execute(string $sqlFilePath): void
    {
        $this->backupRepository->restoreFromSqlFile($sqlFilePath);
    }
}
