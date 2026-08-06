<?php

it('registers certificate edit query parameter on attestation routes', function () {
    expect(route('students.certificate', ['id' => 1, 'attestation' => 5]))
        ->toContain('/students/1/certificate')
        ->toContain('attestation=5')
        ->and(route('students.certificate-with-grades', ['id' => 2, 'attestation' => 7]))
        ->toContain('/students/2/certificate-with-grades')
        ->toContain('attestation=7')
        ->and(route('students.profile.attestations.update', [3, 9]))
        ->toContain('/students/3/profile/attestations/9');
});
