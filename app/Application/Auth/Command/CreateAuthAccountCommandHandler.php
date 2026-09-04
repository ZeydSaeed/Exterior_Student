<?php

namespace App\Application\Auth\Command;

use App\Domain\Auth\AuthAccount;
use App\Domain\Auth\AuthAccountCommandRepository;
use App\Domain\Auth\AuthAccountQueryRepository;
use App\Domain\Auth\PermissionCatalog;
use DomainException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

/**
 * إنشاء حساب موظف أو مسؤول.
 */
final class CreateAuthAccountCommandHandler
{
    public function __construct(
        private AuthAccountCommandRepository $commandRepository,
        private AuthAccountQueryRepository $queryRepository,
    ) {}

    /**
     * @param  list<string>  $permissions
     */
    public function handle(
        string $name,
        string $username,
        string $password,
        bool $isAdmin = false,
        array $permissions = [],
    ): int {
        $account = AuthAccount::create($name, $username, $password, $isAdmin, $permissions);

        if ($this->queryRepository->usernameExists($account->username)) {
            throw new DomainException('اسم الدخول مستخدم مسبقاً');
        }

        $permissions = $isAdmin ? [] : PermissionCatalog::filterValid($permissions);

        return $this->commandRepository->create(
            name: $account->name,
            username: $account->username,
            passwordHash: Hash::make($password),
            passwordDisplayEncrypted: Crypt::encryptString($password),
            isAdmin: $isAdmin,
            permissions: $permissions,
        );
    }
}
