<?php

use App\Infrastructure\Backup\AesGcmBackupFileCipher;

uses(Tests\TestCase::class);

it('encrypts a sql dump so student data is not readable as text', function () {
    $plain = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('backup_plain_', true).'.sql';
    $encrypted = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('backup_enc_', true).'.esbak';
    $restored = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('backup_restored_', true).'.sql';
    $sql = "-- MariaDB dump\nCREATE TABLE `students` (`name` varchar(255));\nINSERT INTO `students` VALUES ('أحمد');\n";
    file_put_contents($plain, $sql);

    $cipher = new AesGcmBackupFileCipher;
    $cipher->encryptFile($plain, $encrypted);

    $encryptedContents = (string) file_get_contents($encrypted);

    expect($cipher->isEncryptedFile($encrypted))->toBeTrue()
        ->and($encryptedContents)->not->toContain('CREATE TABLE')
        ->and($encryptedContents)->not->toContain('أحمد')
        ->and($encryptedContents)->toStartWith('ESBK');

    $cipher->decryptFile($encrypted, $restored);

    expect((string) file_get_contents($restored))->toBe($sql);

    @unlink($plain);
    @unlink($encrypted);
    @unlink($restored);
});

it('rejects a tampered encrypted backup file', function () {
    $plain = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('backup_plain_', true).'.sql';
    $encrypted = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('backup_enc_', true).'.esbak';
    file_put_contents($plain, "-- MariaDB dump\nCREATE TABLE `students` (`id` int);\n");

    $cipher = new AesGcmBackupFileCipher;
    $cipher->encryptFile($plain, $encrypted);

    $contents = (string) file_get_contents($encrypted);
    $index = strlen($contents) - 3;
    $contents[$index] = chr(ord($contents[$index]) ^ 0xFF);
    file_put_contents($encrypted, $contents);

    $cipher->decryptFile($encrypted, $plain);
})->throws(RuntimeException::class, 'تعذر فك تشفير الملف');
