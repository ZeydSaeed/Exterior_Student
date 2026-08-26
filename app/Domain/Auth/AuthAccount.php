<?php

namespace App\Domain\Auth;

use DomainException;

/**
 * كيان حساب الدخول في طبقة الـ Domain.
 */
final class AuthAccount
{
    /**
     * @param  list<string>  $permissions
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $username,
        public readonly string $passwordHash,
        public readonly bool $isAdmin,
        public readonly array $permissions = [],
    ) {}

    /**
     * @param  list<string>  $permissions
     */
    public static function create(
        string $name,
        string $username,
        string $plainPassword,
        bool $isAdmin = false,
        array $permissions = [],
    ): self {
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

        if (strlen($plainPassword) < 6) {
            throw new DomainException('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
        }

        $permissions = $isAdmin ? [] : PermissionCatalog::filterValid($permissions);

        return new self(
            id: 0,
            name: $name,
            username: $username,
            passwordHash: $plainPassword,
            isAdmin: $isAdmin,
            permissions: $permissions,
        );
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin) {
            return true;
        }

        return in_array($permission, $this->permissions, true);
    }
}
