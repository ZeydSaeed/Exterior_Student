<?php

namespace App\Application\Student\Command;

use App\Domain\Student\StudentCommandRepository;

/**
 * أمر إدراج طالب جديد (CQRS — Command).
 */
final class CreateStudentCommandHandler
{
    public function __construct(
        private StudentCommandRepository $repository
    ) {}

    /**
     * @param  array<string, string|null>  $data  الخريطة بالمفاتيح: enrollment_number, exam_number, name_student, name_father, name_grandfather, name_surname, birth_date, birth_place, mother_full_name, gender, branch, major, academic_year, last_school, middle_doc_number, middle_doc_date, issuing_authority
     */
    public function handle(array $data): int
    {
        return $this->repository->create($data);
    }
}
