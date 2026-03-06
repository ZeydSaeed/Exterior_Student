<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StudentCertificateController;
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
