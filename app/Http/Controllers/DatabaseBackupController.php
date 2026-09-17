<?php

namespace App\Http\Controllers;

use App\Application\Backup\BackupSavePathPicker;
use App\Application\Backup\CreateDatabaseBackupUseCase;
use App\Application\Backup\RestoreDatabaseBackupUseCase;
use App\Http\Requests\CreateDatabaseBackupRequest;
use App\Http\Requests\ImportDatabaseBackupRequest;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class DatabaseBackupController extends Controller
{
    public function store(
        CreateDatabaseBackupRequest $request,
        CreateDatabaseBackupUseCase $useCase,
        BackupSavePathPicker $pathPicker,
    ): BinaryFileResponse|JsonResponse|RedirectResponse {
        try {
            $destinationPath = null;

            if ($request->boolean('pick_path')) {
                set_time_limit(300);
                $destinationPath = $pathPicker->pick($this->suggestedBackupFileName());

                if ($destinationPath === null) {
                    return response()->json([
                        'cancelled' => true,
                    ]);
                }
            }

            $result = $useCase->execute($destinationPath);
            $filePath = $result['file_path'];
            $fileName = $result['file_name'];

            if (! is_file($filePath)) {
                throw new RuntimeException('تعذر تجهيز ملف النسخ الاحتياطي.');
            }

            if ($destinationPath !== null) {
                return response()->json([
                    'message' => 'تم حفظ النسخ الاحتياطي في الموقع الذي اخترته.',
                    'file_name' => $fileName,
                ]);
            }

            return response()
                ->download($filePath, $fileName, [
                    'Content-Type' => 'application/octet-stream',
                    'X-Content-Type-Options' => 'nosniff',
                ])
                ->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            $msg = $e->getMessage();

            if ($request->expectsJson() || $request->ajax() || $request->boolean('pick_path')) {
                return response()->json([
                    'message' => 'تعذر إنشاء النسخ الاحتياطي: '.$msg,
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'تعذر إنشاء النسخ الاحتياطي: '.$msg);
        }
    }

    public function restore(
        ImportDatabaseBackupRequest $request,
        RestoreDatabaseBackupUseCase $useCase,
        BackupSavePathPicker $pathPicker,
    ): JsonResponse|RedirectResponse {
        $storedPath = null;
        $deleteAfter = false;

        try {
            if ($request->boolean('pick_path')) {
                set_time_limit(300);
                $chosenPath = $pathPicker->pick('backup.esbak', true);
                if ($chosenPath === null) {
                    return response()->json([
                        'cancelled' => true,
                    ]);
                }

                $this->assertEncryptedBackupPath($chosenPath);
                $storedPath = $chosenPath;
            } else {
                $file = $request->file('file');
                if (! $file instanceof UploadedFile) {
                    throw new RuntimeException('تعذر قراءة الملف المرفوع.');
                }

                $storedPath = $this->storeUploadedBackupFile($file);
                $deleteAfter = true;
            }

            $useCase->execute($storedPath);

            if ($request->expectsJson() || $request->ajax() || $request->boolean('pick_path')) {
                return response()->json([
                    'message' => 'تم استيراد قاعدة البيانات بنجاح.',
                ]);
            }

            return redirect()
                ->back()
                ->with('status', 'تم استيراد قاعدة البيانات بنجاح.');
        } catch (Throwable $e) {
            $msg = 'تعذر استيراد قاعدة البيانات: '.$e->getMessage();

            if ($request->expectsJson() || $request->ajax() || $request->boolean('pick_path')) {
                return response()->json([
                    'message' => $msg,
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', $msg);
        } finally {
            if ($deleteAfter && is_string($storedPath) && is_file($storedPath)) {
                @unlink($storedPath);
            }
        }
    }

    private function suggestedBackupFileName(): string
    {
        $extension = trim((string) config('backup.file_extension', 'esbak'), '.');
        if ($extension === '') {
            $extension = 'esbak';
        }

        return 'backup_exterior_student_'.(new DateTimeImmutable)->format('Y-m-d_H-i-s').'.'.$extension;
    }

    private function storeUploadedBackupFile(UploadedFile $file): string
    {
        $directory = storage_path('app/database-restore');
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('تعذر تجهيز ملف الاستيراد.');
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension !== 'esbak') {
            throw new RuntimeException('يجب أن يكون الملف نسخة احتياطية مشفّرة بصيغة .esbak.');
        }

        $storedPath = $directory.DIRECTORY_SEPARATOR.uniqid('restore_', true).'.'.$extension;
        $file->move($directory, basename($storedPath));

        if (! is_file($storedPath)) {
            throw new RuntimeException('تعذر حفظ الملف المرفوع.');
        }

        return $storedPath;
    }

    private function assertEncryptedBackupPath(string $path): void
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('ملف النسخة الاحتياطية غير موجود أو غير قابل للقراءة.');
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if ($extension !== 'esbak') {
            throw new RuntimeException('يجب أن يكون الملف نسخة احتياطية مشفّرة بصيغة .esbak.');
        }
    }
}
