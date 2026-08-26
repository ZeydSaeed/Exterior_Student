<?php

use App\Domain\Auth\PermissionCatalog;
use App\Models\User;

it('hides grades edit controls in the modal when the grades edit permission is checked in the template', function () {
    $html = file_get_contents(resource_path('views/students/partials/grades-modal.blade.php'));

    expect($html)->toContain('PermissionCatalog::STUDENTS_GRADES_EDIT')
        ->and($html)->toContain('grades-btn-edit');
});

it('gates the students table edit action behind the grades edit permission', function () {
    $html = file_get_contents(resource_path('views/students/partials/table.blade.php'));

    expect($html)->toContain('PermissionCatalog::STUDENTS_GRADES_EDIT')
        ->and($html)->toContain('btn-edit-row')
        ->and($html)->toContain('PermissionCatalog::STUDENTS_DELETE');
});

it('allows admins to edit grades and denies staff without the permission', function () {
    $admin = new User;
    $admin->forceFill([
        'id' => 1,
        'name' => 'Admin',
        'username' => 'admin',
        'is_admin' => true,
    ]);
    $admin->exists = true;

    $staff = new User;
    $staff->forceFill([
        'id' => 2,
        'name' => 'Staff',
        'username' => 'staff',
        'is_admin' => false,
    ]);
    $staff->exists = true;
    $staff->setRelation('permissionRecords', collect());

    expect($admin->hasPermission(PermissionCatalog::STUDENTS_GRADES_EDIT))->toBeTrue()
        ->and($staff->hasPermission(PermissionCatalog::STUDENTS_GRADES_EDIT))->toBeFalse();
});
