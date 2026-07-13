<?php

namespace App\Application\Student\DTO;

use App\Application\Record\DTO\RecordDTO;

/**
 * DTO لعرض صفحة سجل قيد الطالب (قيد الطالب) — معلومات كاملة + جدول الدرجات + الوثائق + التواقيع.
 */
final class StudentDocumentPageDTO
{
    /**
     * @param  array<int, array{subject: string, score: string, score_words: string}>  $gradesTable
     * @param  list<string>  $subjectsCompleted  الدروس التي أكمل بها (الدرجة أقل من 50)
     * @param  list<RecordDTO>  $documents
     * @param  list<array{type: string, name: string}>  $employees
     */
    public function __construct(
        public int $studentId,
        public string $fullName,
        public string $examNumber,
        public string $birthDate,
        public string $birthPlace,
        public string $motherName,
        public string $branch,
        public string $specialization,
        public string $lastSchool,
        public string $middleDocNumber,
        public string $middleDocDate,
        public string $issuingAuthority,
        public string $academicYear,
        public string $result,
        public string $round,
        public string $gender,
        public string $pageNumber,
        public string $enrollmentNumber,
        public array $gradesTable,
        public string $total,
        public string $totalWords,
        public array $subjectsCompleted,
        public array $documents,
        public array $employees,
    ) {}

    /**
     * قيد فارغ قابل للطباعة (عند فتح صفحة القيود بدون فلاتر).
     */
    public static function blank(int $emptyGradeRows = 8): self
    {
        $gradesTable = [];
        for ($i = 0; $i < $emptyGradeRows; $i++) {
            $gradesTable[] = [
                'subject' => '',
                'score' => '',
                'score_words' => '',
            ];
        }

        return new self(
            studentId: 0,
            fullName: '',
            examNumber: '',
            birthDate: '',
            birthPlace: '',
            motherName: '',
            branch: '',
            specialization: '',
            lastSchool: '',
            middleDocNumber: '',
            middleDocDate: '',
            issuingAuthority: '',
            academicYear: '',
            result: '',
            round: '',
            gender: '',
            pageNumber: '',
            enrollmentNumber: '',
            gradesTable: $gradesTable,
            total: '',
            totalWords: '',
            subjectsCompleted: [],
            documents: [],
            employees: [
                ['type' => 'مدير قسم التعليم المهني', 'name' => ''],
                ['type' => 'الموظف المسؤول', 'name' => ''],
            ],
        );
    }
}
