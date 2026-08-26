<?php

namespace App\Domain\Auth;

interface AuthAccountCommandRepository
{
    /**
     * @param  list<string>  $permissions
     */
    public function create(
        string $name,
        string $username,
        string $passwordHash,
        bool $isAdmin,
        array $permissions,
    ): int;

    /**
     * @param  list<string>  $permissions
     */
    public function update(
        int $id,
        string $name,
        string $username,
        ?string $passwordHash,
        bool $isAdmin,
        array $permissions,
    ): void;

    public function delete(int $id): void;
}
