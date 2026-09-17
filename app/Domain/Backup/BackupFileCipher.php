<?php

namespace App\Domain\Backup;

interface BackupFileCipher
{
    public function encryptFile(string $plainPath, string $encryptedPath): void;

    public function decryptFile(string $encryptedPath, string $plainPath): void;

    public function isEncryptedFile(string $path): bool;
}
