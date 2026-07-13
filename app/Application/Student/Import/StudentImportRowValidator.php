<?php

namespace App\Application\Student\Import;

use App\Domain\Student\BranchMajorCatalogInterface;
use App\Domain\Student\StudentQueryRepository;
use App\Support\ImportDateNormalizer;

/**
 * التحقق من صف استيراد طالب واحد (قواعد: الرقم الامتحاني مطلوب، الجنس، التولد، علاقة الاختصاص بالفرع، عدم التكرار).
 */
final class StudentImportRowValidator
{
    private const GENDER_VALID = ['ذكر', 'أنثى', 'انثى'];

    public function __construct(
        private StudentQueryRepository $queryRepository,
        private BranchMajorCatalogInterface $branchMajorCatalog
    ) {}

    /**
     * @param  array{exam_number?: ?string, first_name?: ?string, father?: ?string, grandfather?: ?string, last_name?: ?string, mother?: ?string, birth_date?: ?string, birth_place?: ?string, gender?: ?string, branch?: ?string, major?: ?string, academic_year?: ?string, last_school?: ?string, document_number?: ?string, document_date?: ?string, issue_place?: ?string}  $row
     * @return list<string> قائمة رسائل الأخطاء (فارغة = صالح)
     */
    public function validate(array $row): array
    {
        $errors = [];
        $examNumber = trim((string) ($row['exam_number'] ?? ''));
        if ($examNumber === '') {
            $errors[] = 'الرقم الامتحاني مطلوب';
        } else {
            if ($this->queryRepository->existsExamNumber($examNumber)) {
                $errors[] = 'الرقم الامتحاني مكرر';
            }
        }

        $gender = trim((string) ($row['gender'] ?? ''));
        if ($gender === '') {
            $errors[] = 'الجنس مطلوب';
        } elseif (! in_array($gender, self::GENDER_VALID, true)) {
            $errors[] = 'الجنس غير صالح (يجب: ذكر، أنثى، انثى)';
        }

        $birthDate = isset($row['birth_date']) ? trim((string) $row['birth_date']) : '';
        if ($birthDate === '') {
            $errors[] = 'التولد مطلوب';
        } elseif ($this->normalizeDate($birthDate) === null) {
            $errors[] = 'تاريخ التولد غير صالح';
        }

        $branch = trim((string) ($row['branch'] ?? ''));
        $major = trim((string) ($row['major'] ?? ''));
        if ($branch !== '' && $major !== '' && ! $this->branchMajorCatalog->majorBelongsToBranch($major, $branch)) {
            $errors[] = 'الاختصاص لا يتبع الفرع المحدد';
        }

        $academicYear = trim((string) ($row['academic_year'] ?? ''));
        if ($academicYear === '' && ($branch !== '' || $major !== '')) {
            $errors[] = 'العام الدراسي مطلوب عند تحديد الفرع/الاختصاص';
        }

        return $errors;
    }

    private function normalizeDate(string $value): ?string
    {
        return ImportDateNormalizer::toYmd($value);
    }
}
