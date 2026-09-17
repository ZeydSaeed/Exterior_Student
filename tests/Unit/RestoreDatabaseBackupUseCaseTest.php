<?php

use App\Application\Backup\RestoreDatabaseBackupUseCase;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;

it('delegates restore to the backup repository', function () {
    $repository = Mockery::mock(DatabaseBackupRepository::class);
    $repository->shouldReceive('restoreFromSqlFile')
        ->once()
        ->with('D:\\backups\\backup.sql');

    $useCase = new RestoreDatabaseBackupUseCase($repository);
    $useCase->execute('D:\\backups\\backup.sql');
});
