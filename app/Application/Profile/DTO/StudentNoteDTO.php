<?php

namespace App\Application\Profile\DTO;

/**
 * DTO لعرض ملاحظة واحدة في السجل الشخصي.
 */
final class StudentNoteDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $body,
    ) {}
}
