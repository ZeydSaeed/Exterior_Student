<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StudentCertificateController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentRecordsController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/students', [StudentController::class, 'index'])
    ->name('students.index');

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
