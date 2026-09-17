<?php

namespace App\Infrastructure\Backup;

use App\Domain\Backup\BackupFileCipher;
use RuntimeException;

/**
 * تشفير ملف النسخة الاحتياطية بـ AES-256-GCM مع التحقق من سلامة المحتوى.
 */
final class AesGcmBackupFileCipher implements BackupFileCipher
{
    private const MAGIC = 'ESBK';

    private const VERSION = 1;

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    private const HEADER_LENGTH = 4 + 1 + self::IV_LENGTH + self::TAG_LENGTH;

    private const AAD = 'exterior-student-backup';

    public function encryptFile(string $plainPath, string $encryptedPath): void
    {
        $plaintext = $this->readFile($plainPath, 'تعذر قراءة ملف النسخ الاحتياطي قبل التشفير.');
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $this->rawKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::AAD
        );

        if ($ciphertext === false || strlen($tag) !== self::TAG_LENGTH) {
            throw new RuntimeException('تعذر تشفير ملف النسخ الاحتياطي.');
        }

        $written = file_put_contents($encryptedPath, self::MAGIC.chr(self::VERSION).$iv.$tag.$ciphertext);
        if ($written === false) {
            throw new RuntimeException('تعذر حفظ ملف النسخ الاحتياطي المشفّر.');
        }
    }

    public function decryptFile(string $encryptedPath, string $plainPath): void
    {
        $payload = $this->readFile($encryptedPath, 'تعذر قراءة ملف النسخ الاحتياطي المشفّر.');
        if (! $this->hasValidHeader($payload)) {
            throw new RuntimeException('ملف النسخ الاحتياطي المشفّر غير صالح أو تالف.');
        }

        $iv = substr($payload, 5, self::IV_LENGTH);
        $tag = substr($payload, 5 + self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($payload, self::HEADER_LENGTH);
        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $this->rawKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::AAD
        );

        if ($plaintext === false) {
            throw new RuntimeException('تعذر فك تشفير الملف. قد يكون معدّلاً أو لا ينتمي لهذا النظام.');
        }

        $written = file_put_contents($plainPath, $plaintext);
        if ($written === false) {
            throw new RuntimeException('تعذر تجهيز ملف الاستيراد بعد فك التشفير.');
        }
    }

    public function isEncryptedFile(string $path): bool
    {
        if (! is_file($path) || ! is_readable($path)) {
            return false;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        try {
            $head = (string) fread($handle, self::HEADER_LENGTH);

            return $this->hasValidHeader($head);
        } finally {
            fclose($handle);
        }
    }

    private function hasValidHeader(string $payload): bool
    {
        return strlen($payload) >= self::HEADER_LENGTH
            && str_starts_with($payload, self::MAGIC)
            && ord($payload[4]) === self::VERSION;
    }

    private function readFile(string $path, string $error): string
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException($error);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException($error);
        }

        return $contents;
    }

    private function rawKey(): string
    {
        $key = (string) config('backup.encryption_key');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if (is_string($decoded) && $decoded !== '') {
                $key = $decoded;
            }
        }

        if (trim($key) === '') {
            throw new RuntimeException('مفتاح تشفير النسخ الاحتياطي غير مهيأ.');
        }

        return hash('sha256', $key, true);
    }
}
