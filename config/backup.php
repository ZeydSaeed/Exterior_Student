<?php

return [
    /**
     * امتداد ملف النسخة الاحتياطية المشفّرة (غير قابل للقراءة كنص SQL).
     */
    'file_extension' => 'esbak',

    /**
     * مفتاح تشفير النسخ الاحتياطي. إن تُرك فارغاً يُشتق من APP_KEY.
     * يُفضّل تثبيته حتى تعمل ملفات .esbak على أي نسخة منصّبة من البرنامج.
     */
    'encryption_key' => env('BACKUP_ENCRYPTION_KEY', env('APP_KEY')),
];
