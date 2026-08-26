<?php

namespace App\Domain\Auth;

interface AuthAccountQueryRepository
{
    /**
     * @return list<array{id: int, name: string, username: string, is_admin: bool, permissions: list<string>}>
     */
    public function listAccounts(): array;

    /**
     * @return array{id: int, name: string, username: string, is_admin: bool, permissions: list<string>}|null
     */
    public function findById(int $id): ?array;

    /**
     * @return array{id: int, name: string, username: string, password: string, is_admin: bool, permissions: list<string>}|null
     */
    public function findByUsername(string $username): ?array;

    public function usernameExists(string $username, ?int $exceptId = null): bool;
}
