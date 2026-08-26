<?php

use App\Domain\Auth\PermissionCatalog;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StudentCertificateController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDocumentsBulkPrintController;
use App\Http\Controllers\StudentExcelImportController;
use App\Http\Controllers\StudentFailuresBulkDeleteController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentRecordPrintController;
use App\Http\Controllers\StudentRecordsController;
use App\Http\Controllers\StudentRepeatersController;
use App\Http\Controllers\StudentResultsExcelImportController;
use App\Http\Controllers\StudentStatisticsController;
use App\Http\Controllers\UserAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('permission:'.PermissionCatalog::NAV_STUDENTS)->group(function (): void {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    });

    Route::middleware('permission:'.PermissionCatalog::NAV_DOCUMENTS_BULK)->group(function (): void {
        Route::get('/students/documents/print', [StudentDocumentsBulkPrintController::class, '__invoke'])
            ->name('students.documents.bulk-print');
        Route::get('/students/documents/print/chunk', [StudentDocumentsBulkPrintController::class, 'chunk'])
            ->name('students.documents.bulk-print.chunk');
    });

    Route::middleware('permission:'.PermissionCatalog::NAV_FAILURES)->group(function (): void {
        Route::delete('/students/failures', StudentFailuresBulkDeleteController::class)
            ->name('students.failures.destroy');
    });

    Route::middleware('permission:'.PermissionCatalog::NAV_REPEATERS)->group(function (): void {
        Route::get('/students/repeaters', [StudentRepeatersController::class, 'index'])
            ->name('students.repeaters.index');
    });

    Route::middleware('permission:'.PermissionCatalog::NAV_STATISTICS)->group(function (): void {
        Route::get('/students/statistics', [StudentStatisticsController::class, 'index'])
            ->name('students.statistics.index');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_CREATE)->group(function (): void {
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    });

    Route::middleware('permission:'.PermissionCatalog::NAV_IMPORT_STUDENTS)->group(function (): void {
        Route::get('/students/import-excel', [StudentExcelImportController::class, 'show'])
            ->name('students.import-excel');
        Route::post('/students/import-excel', [StudentExcelImportController::class, 'upload'])
            ->name('students.import-excel.upload');
        Route::get('/students/import-excel/preview', [StudentExcelImportController::class, 'preview'])
            ->name('students.import-excel.preview');
        Route::post('/students/import-excel/process', [StudentExcelImportController::class, 'process'])
            ->name('students.import-excel.process');
    });

    Route::middleware('permission:'.PermissionCatalog::NAV_IMPORT_RESULTS)->group(function (): void {
        Route::get('/students/results-import-excel', [StudentResultsExcelImportController::class, 'show'])
            ->name('students.results-import-excel');
        Route::post('/students/results-import-excel', [StudentResultsExcelImportController::class, 'upload'])
            ->name('students.results-import-excel.upload');
        Route::get('/students/results-import-excel/preview', [StudentResultsExcelImportController::class, 'preview'])
            ->name('students.results-import-excel.preview');
        Route::post('/students/results-import-excel/process', [StudentResultsExcelImportController::class, 'process'])
            ->name('students.results-import-excel.process');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_GRADES_VIEW)->group(function (): void {
        Route::get('/students/{id}/grades', [StudentController::class, 'grades'])->name('students.grades');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_GRADES_EDIT)->group(function (): void {
        Route::put('/students/{id}/grades', [StudentController::class, 'updateGrades'])
            ->name('students.grades.update');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_DELETE)->group(function (): void {
        Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_CERTIFICATE_VIEW)->group(function (): void {
        Route::get('/students/{id}/certificate', [StudentCertificateController::class, 'show'])
            ->name('students.certificate');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_CERTIFICATE_GRADES_VIEW)->group(function (): void {
        Route::get('/students/{id}/certificate-with-grades', [StudentCertificateController::class, 'showWithGrades'])
            ->name('students.certificate-with-grades');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_DOCUMENT_VIEW)->group(function (): void {
        Route::get('/students/{id}/document', [StudentRecordPrintController::class, 'show'])
            ->name('students.document');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_DOCUMENT_EDIT)->group(function (): void {
        Route::put('/students/{id}/document', [StudentRecordPrintController::class, 'update'])
            ->name('students.document.update');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_DOCUMENTS_VIEW)->group(function (): void {
        Route::get('/students/{id}/documents', [StudentRecordsController::class, 'index'])
            ->name('students.documents.index');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_DOCUMENTS_CREATE)->group(function (): void {
        Route::post('/students/{id}/documents', [StudentRecordsController::class, 'store'])
            ->name('students.documents.store');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_DOCUMENTS_EDIT)->group(function (): void {
        Route::put('/students/{id}/documents/{recordId}', [StudentRecordsController::class, 'update'])
            ->name('students.documents.update');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_DOCUMENTS_DELETE)->group(function (): void {
        Route::delete('/students/{id}/documents/{recordId}', [StudentRecordsController::class, 'destroy'])
            ->name('students.documents.destroy');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_PROFILE_VIEW)->group(function (): void {
        Route::get('/students/{id}/profile', [StudentProfileController::class, 'show'])
            ->name('students.profile.show');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_PROFILE_ATTESTATION_CREATE)->group(function (): void {
        Route::post('/students/{id}/profile/attestations', [StudentProfileController::class, 'storeAttestation'])
            ->name('students.profile.attestations.store');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_PROFILE_ATTESTATION_EDIT)->group(function (): void {
        Route::put('/students/{id}/profile/attestations/{attestationId}', [StudentProfileController::class, 'updateAttestation'])
            ->name('students.profile.attestations.update');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_PROFILE_ATTESTATION_DELETE)->group(function (): void {
        Route::delete('/students/{id}/profile/attestations/{attestationId}', [StudentProfileController::class, 'destroyAttestation'])
            ->name('students.profile.attestations.destroy');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_PROFILE_NOTE_CREATE)->group(function (): void {
        Route::post('/students/{id}/profile/notes', [StudentProfileController::class, 'storeNote'])
            ->name('students.profile.notes.store');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_PROFILE_NOTE_EDIT)->group(function (): void {
        Route::put('/students/{id}/profile/notes/{noteId}', [StudentProfileController::class, 'updateNote'])
            ->name('students.profile.notes.update');
    });

    Route::middleware('permission:'.PermissionCatalog::STUDENTS_PROFILE_NOTE_DELETE)->group(function (): void {
        Route::delete('/students/{id}/profile/notes/{noteId}', [StudentProfileController::class, 'destroyNote'])
            ->name('students.profile.notes.destroy');
    });

    Route::middleware('permission:'.PermissionCatalog::EMPLOYEES_MANAGE)->group(function (): void {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::post('/employees/signatures', [EmployeeController::class, 'storeSignatures'])
            ->name('employees.signatures.store');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    Route::middleware('permission:'.PermissionCatalog::BACKUP_CREATE)->group(function (): void {
        Route::post('/database/backup', [DatabaseBackupController::class, 'store'])
            ->name('database-backup.store');
    });

    Route::middleware('permission:'.PermissionCatalog::USERS_MANAGE)->group(function (): void {
        Route::get('/accounts', [UserAccountController::class, 'index'])->name('accounts.index');
        Route::post('/accounts', [UserAccountController::class, 'store'])->name('accounts.store');
        Route::put('/accounts/{id}', [UserAccountController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{id}', [UserAccountController::class, 'destroy'])->name('accounts.destroy');
    });
});
