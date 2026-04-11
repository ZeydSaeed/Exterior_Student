<?php

namespace App\Application\Backup;

use App\Domain\Backup\Repositories\DatabaseBackupRepository;

final class CreateDatabaseBackupUseCase
{
    public function __construct(
        private DatabaseBackupRepository $backupRepository,
    ) {}

    /**
     * @return array{file_path:string,file_name:string,size_bytes:int}
     */
    public function execute(): array
    {
        $destinationDir = base_path('backup');

        return $this->backupRepository->createBackup($destinationDir);
    }
}
