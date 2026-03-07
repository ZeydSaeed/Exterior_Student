<?php

namespace App\Application\Record\Command;

use App\Domain\Record\RecordCommandRepository;

/**
 * أمر حذف وثيقة طالب (CQRS — Command).
 */
final class DeleteRecordCommandHandler
{
    public function __construct(
        private RecordCommandRepository $repository
    ) {}

    public function handle(int $recordId): void
    {
        $this->repository->delete($recordId);
    }
}
