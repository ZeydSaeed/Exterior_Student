<?php

use App\Http\Requests\StoreAttestationRequest;
use App\Http\Requests\UpdateAttestationRequest;
use App\Support\ArabicDigits;
use Illuminate\Support\Facades\Validator;

it('renders attestation number fields with arabic digit inputs', function () {
    $profile = file_get_contents(resource_path('views/students/profile.blade.php'));
    $withoutGrades = file_get_contents(resource_path('views/students/certificate.blade.php'));
    $withGrades = file_get_contents(resource_path('views/students/certificate-with-grades.blade.php'));

    expect($profile)->toContain('name="number"')
        ->and($profile)->toContain('arabic-digits-input')
        ->and($profile)->toContain('ArabicDigits::toArabic($att->number')
        ->and($withoutGrades)->toContain('id="cert-field-number"')
        ->and($withoutGrades)->toContain('arabic-digits-input')
        ->and($withoutGrades)->toContain('ArabicDigits::toArabic($attestation->number')
        ->and($withGrades)->toContain('id="cert-field-number"')
        ->and($withGrades)->toContain('arabic-digits-input')
        ->and($withGrades)->toContain('ArabicDigits::toArabic($attestation->number');
});

it('normalizes arabic attestation numbers to western digits before validation', function () {
    $update = UpdateAttestationRequest::create('/', 'PUT', [
        'number' => '١٢٣٤',
    ]);
    $update->setContainer(app());
    $update->setRedirector(app('redirect'));

    $updateValidator = Validator::make($update->all(), $update->rules());
    $update->setValidator($updateValidator);

    $ref = new ReflectionClass($update);
    $method = $ref->getMethod('prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($update);

    expect($update->input('number'))->toBe('1234')
        ->and(ArabicDigits::toArabic('1234'))->toBe('١٢٣٤');

    $store = StoreAttestationRequest::create('/', 'POST', [
        'type' => 'without_grades',
        'number' => '٨٨',
    ]);
    $store->setContainer(app());
    $store->setRedirector(app('redirect'));

    $storeRef = new ReflectionClass($store);
    $storeMethod = $storeRef->getMethod('prepareForValidation');
    $storeMethod->setAccessible(true);
    $storeMethod->invoke($store);

    expect($store->input('number'))->toBe('88');
});
