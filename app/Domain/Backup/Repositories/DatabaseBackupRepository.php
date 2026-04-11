<?php

namespace App\Domain\Backup\Repositories;

interface DatabaseBackupRepository
{
    /**
     * @return array{file_path:string,file_name:string,size_bytes:int}
     */
    public function createBackup(string $destinationDir): array;
}
