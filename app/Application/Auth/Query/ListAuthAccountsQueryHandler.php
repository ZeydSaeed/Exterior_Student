<?php

namespace App\Application\Auth\Query;

use App\Domain\Auth\AuthAccountQueryRepository;
use App\Domain\Auth\PermissionCatalog;

/**
 * قائمة الحسابات مع كتالوج الصلاحيات للواجهة.
 */
final class ListAuthAccountsQueryHandler
{
    public function __construct(
        private AuthAccountQueryRepository $queryRepository,
    ) {}

    /**
     * @return array{accounts: list<array{id: int, name: string, username: string, is_admin: bool, password_display: string|null, permissions: list<string>}>, permission_groups: array<string, array{label: string, permissions: array<string, string>}>}
     */
    public function handle(): array
    {
        return [
            'accounts' => $this->queryRepository->listAccounts(),
            'permission_groups' => PermissionCatalog::groups(),
        ];
    }
}
