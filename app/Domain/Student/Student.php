<?php

namespace App\Domain\Student;

use DomainException;

/**
 * كيان الطالب (قراءة للعرض)
 */
final class Student
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $exam_number,
        public readonly ?string $full_name,
        public readonly ?string $academic_year,
        public readonly ?string $result,
        public readonly ?string $branch,
        public readonly ?string $major,
        public readonly ?string $gender,
    ) {}

    public static function fromObject(object $row): self
    {
        return new self(
            id: (int) ($row->id ?? 0),
            exam_number: $row->exam_number ?? null,
            full_name: $row->full_name ?? null,
            academic_year: $row->academic_year ?? null,
            result: $row->result ?? null,
            branch: $row->branch ?? null,
            major: $row->major ?? null,
            gender: $row->gender ?? null,
        );
    }

    /**
     * التحقق من إمكانية حذف هذا الطالب وفق قواعد الدومين.
     * يمكن توسيع هذه القواعد لاحقاً (مثلاً التحقق من وجود وثائق، أو إغلاق السنة الدراسية).
     *
     * @throws DomainException إذا لم يكن الحذف مسموحاً
     */
    public function ensureCanBeDeleted(): void
    {
        // مثال بسيط: منع حذف طالب نتيجته "ناجح"
        if ($this->result !== null && trim($this->result) !== '' && $this->result === 'ناجح') {
            throw new DomainException('لا يمكن حذف طالب ناجح.');
        }

        // يمكن إضافة قواعد إضافية هنا مستقبلاً
    }
}
