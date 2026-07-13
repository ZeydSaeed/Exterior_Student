<?php

use App\Application\Student\DTO\StudentDocumentPageDTO;

it('builds a blank printable student document dto', function () {
    $dto = StudentDocumentPageDTO::blank();

    expect($dto->studentId)->toBe(0)
        ->and($dto->fullName)->toBe('')
        ->and($dto->examNumber)->toBe('')
        ->and($dto->gradesTable)->toHaveCount(8);
});
