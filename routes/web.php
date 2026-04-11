<?php

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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/students', [StudentController::class, 'index'])
    ->name('students.index');

Route::get('/students/documents/print', StudentDocumentsBulkPrintController::class)
    ->name('students.documents.bulk-print');

Route::delete('/students/failures', StudentFailuresBulkDeleteController::class)
    ->name('students.failures.destroy');

Route::get('/students/repeaters', [StudentRepeatersController::class, 'index'])
    ->name('students.repeaters.index');

Route::get('/students/create', [StudentController::class, 'create'])
    ->name('students.create');
Route::post('/students', [StudentController::class, 'store'])
    ->name('students.store');

Route::get('/students/import-excel', [StudentExcelImportController::class, 'show'])
    ->name('students.import-excel');
Route::post('/students/import-excel', [StudentExcelImportController::class, 'upload'])
    ->name('students.import-excel.upload');
Route::get('/students/import-excel/preview', [StudentExcelImportController::class, 'preview'])
    ->name('students.import-excel.preview');
Route::post('/students/import-excel/process', [StudentExcelImportController::class, 'process'])
    ->name('students.import-excel.process');

Route::get('/students/results-import-excel', [StudentResultsExcelImportController::class, 'show'])
    ->name('students.results-import-excel');
Route::post('/students/results-import-excel', [StudentResultsExcelImportController::class, 'upload'])
    ->name('students.results-import-excel.upload');
Route::get('/students/results-import-excel/preview', [StudentResultsExcelImportController::class, 'preview'])
    ->name('students.results-import-excel.preview');
Route::post('/students/results-import-excel/process', [StudentResultsExcelImportController::class, 'process'])
    ->name('students.results-import-excel.process');

Route::get('/students/{id}/grades', [StudentController::class, 'grades'])
    ->name('students.grades');

Route::put('/students/{id}/grades', [StudentController::class, 'updateGrades'])
    ->name('students.grades.update');

Route::delete('/students/{id}', [StudentController::class, 'destroy'])
    ->name('students.destroy');

Route::get('/students/{id}/certificate', [StudentCertificateController::class, 'show'])
    ->name('students.certificate');

Route::get('/students/{id}/certificate-with-grades', [StudentCertificateController::class, 'showWithGrades'])
    ->name('students.certificate-with-grades');

Route::get('/students/{id}/document', [StudentRecordPrintController::class, 'show'])
    ->name('students.document');

Route::get('/students/{id}/documents', [StudentRecordsController::class, 'index'])
    ->name('students.documents.index');
Route::post('/students/{id}/documents', [StudentRecordsController::class, 'store'])
    ->name('students.documents.store');
Route::put('/students/{id}/documents/{recordId}', [StudentRecordsController::class, 'update'])
    ->name('students.documents.update');
Route::delete('/students/{id}/documents/{recordId}', [StudentRecordsController::class, 'destroy'])
    ->name('students.documents.destroy');

Route::get('/students/{id}/profile', [StudentProfileController::class, 'show'])
    ->name('students.profile.show');
Route::post('/students/{id}/profile/attestations', [StudentProfileController::class, 'storeAttestation'])
    ->name('students.profile.attestations.store');
Route::put('/students/{id}/profile/attestations/{attestationId}', [StudentProfileController::class, 'updateAttestation'])
    ->name('students.profile.attestations.update');
Route::delete('/students/{id}/profile/attestations/{attestationId}', [StudentProfileController::class, 'destroyAttestation'])
    ->name('students.profile.attestations.destroy');

Route::get('/employees', [EmployeeController::class, 'index'])
    ->name('employees.index');

Route::post('/employees', [EmployeeController::class, 'store'])
    ->name('employees.store');

Route::post('/employees/signatures', [EmployeeController::class, 'storeSignatures'])
    ->name('employees.signatures.store');

Route::put('/employees/{id}', [EmployeeController::class, 'update'])
    ->name('employees.update');

Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])
    ->name('employees.destroy');

Route::post('/database/backup', [DatabaseBackupController::class, 'store'])
    ->name('database-backup.store');
