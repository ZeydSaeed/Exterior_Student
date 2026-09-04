<?php

namespace App\Application\Auth\Command;

use App\Domain\Auth\AuthAccountCommandRepository;
use App\Domain\Auth\AuthAccountQueryRepository;
use App\Domain\Auth\PermissionCatalog;
use DomainException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

/**
 * تحديث حساب وحفظ صلاحياته.
 */
final class UpdateAuthAccountCommandHandler
{
    public function __construct(
        private AuthAccountCommandRepository $commandRepository,
        private AuthAccountQueryRepository $queryRepository,
    ) {}

    /**
     * @param  list<string>  $permissions
     */
    public function handle(
        int $id,
        string $name,
        string $username,
        ?string $password,
        bool $isAdmin,
        array $permissions,
    ): void {
        $existing = $this->queryRepository->findById($id);
        if ($existing === null) {
            throw new DomainException('الحساب غير موجود');
        }

        $name = trim($name);
        $username = trim($username);

        if ($name === '') {
            throw new DomainException('اسم المستخدم المعروض مطلوب');
        }

        if ($username === '') {
            throw new DomainException('اسم الدخول مطلوب');
        }

        if (strlen($username) < 3) {
            throw new DomainException('اسم الدخول يجب أن يكون 3 أحرف على الأقل');
        }

        if ($this->queryRepository->usernameExists($username, $id)) {
            throw new DomainException('اسم الدخول مستخدم مسبقاً');
        }

        $passwordHash = null;
        $passwordDisplayEncrypted = null;
        if ($password !== null && trim($password) !== '') {
            if (strlen($password) < 6) {
                throw new DomainException('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
            }
            $passwordHash = Hash::make($password);
            $passwordDisplayEncrypted = Crypt::encryptString($password);
        }

        $permissions = $isAdmin ? [] : PermissionCatalog::filterValid($permissions);

        $this->commandRepository->update(
            id: $id,
            name: $name,
            username: $username,
            passwordHash: $passwordHash,
            passwordDisplayEncrypted: $passwordDisplayEncrypted,
            isAdmin: $isAdmin,
            permissions: $permissions,
        );
    }
}
