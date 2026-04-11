<?php

namespace App\Http\Controllers;

use App\Application\Backup\CreateDatabaseBackupUseCase;
use Illuminate\Http\RedirectResponse;
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
}
