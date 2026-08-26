<?php

namespace App\Application\Auth\Command;

use App\Domain\Auth\AuthAccountCommandRepository;
use App\Domain\Auth\AuthAccountQueryRepository;
use DomainException;

/**
 * حذف حساب (مع منع حذف آخر مسؤول).
 */
final class DeleteAuthAccountCommandHandler
{
    public function __construct(
        private AuthAccountCommandRepository $commandRepository,
        private AuthAccountQueryRepository $queryRepository,
    ) {}

    public function handle(int $id, ?int $currentUserId = null): void
    {
        $existing = $this->queryRepository->findById($id);
        if ($existing === null) {
            throw new DomainException('الحساب غير موجود');
        }

        if ($currentUserId !== null && $currentUserId === $id) {
            throw new DomainException('لا يمكن حذف الحساب الذي تستخدمه حالياً');
        }

        if ($existing['is_admin']) {
            $admins = array_filter(
                $this->queryRepository->listAccounts(),
                static fn (array $account): bool => $account['is_admin']
            );
            if (count($admins) <= 1) {
                throw new DomainException('لا يمكن حذف المسؤول الوحيد في النظام');
            }
        }

        $this->commandRepository->delete($id);
    }
}
