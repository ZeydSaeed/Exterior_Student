<?php

namespace App\Http\Controllers;

use App\Application\Backup\CreateDatabaseBackupUseCase;
use App\Application\Backup\RestoreDatabaseBackupUseCase;
use App\Http\Requests\ImportDatabaseBackupRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

final class DatabaseBackupController extends Controller
{
    public function store(CreateDatabaseBackupUseCase $useCase): RedirectResponse
    {
        try {
            $result = $useCase->execute();

            return redirect()
                ->back()
                ->with('status', 'تم إنشاء النسخ الاحتياطي بنجاح: '.$result['file_name']);
        } catch (Throwable $e) {
            $msg = $e->getMessage();

            return redirect()
                ->back()
                ->with('error', 'تعذر إنشاء النسخ الاحتياطي: '.$msg);
        }
    }

    public function restore(ImportDatabaseBackupRequest $request, RestoreDatabaseBackupUseCase $useCase): RedirectResponse
    {
        $storedPath = null;

        try {
            $file = $request->file('file');
            if (! $file instanceof UploadedFile) {
                throw new RuntimeException('تعذر قراءة الملف المرفوع.');
            }

            $storedPath = $this->storeUploadedSqlFile($file);
            $useCase->execute($storedPath);

            return redirect()
                ->back()
                ->with('status', 'تم استيراد قاعدة البيانات بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'تعذر استيراد قاعدة البيانات: '.$e->getMessage());
        } finally {
            if (is_string($storedPath) && is_file($storedPath)) {
                @unlink($storedPath);
            }
        }
    }

    private function storeUploadedSqlFile(UploadedFile $file): string
    {
        $directory = storage_path('app/database-restore');
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('تعذر تجهيز ملف الاستيراد.');
        }

        $storedPath = $directory.DIRECTORY_SEPARATOR.uniqid('restore_', true).'.sql';
        $file->move($directory, basename($storedPath));

        if (! is_file($storedPath)) {
            throw new RuntimeException('تعذر حفظ الملف المرفوع.');
        }

        return $storedPath;
    }
}
