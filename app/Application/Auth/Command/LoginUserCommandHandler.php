<?php

namespace App\Application\Auth\Command;

use App\Domain\Auth\AuthAccountQueryRepository;
use DomainException;
use Illuminate\Support\Facades\Hash;

/**
 * التحقق من بيانات الدخول وإرجاع معرف الحساب.
 */
final class LoginUserCommandHandler
{
    public function __construct(
        private AuthAccountQueryRepository $queryRepository,
    ) {}

    public function handle(string $username, string $password): int
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            throw new DomainException('اسم الدخول وكلمة المرور مطلوبان');
        }

        $account = $this->queryRepository->findByUsername($username);
        if ($account === null) {
            throw new DomainException('اسم الدخول أو كلمة المرور غير صحيحة');
        }

        if (! Hash::check($password, $account['password'])) {
            throw new DomainException('اسم الدخول أو كلمة المرور غير صحيحة');
        }

        return (int) $account['id'];
    }
}
