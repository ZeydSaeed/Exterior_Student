<?php

namespace App\Application\Employee\DTO;

/**
 * DTO لعرض / إرجاع بيانات موظف واحد.
 */
final class EmployeeDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $name,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
        ];
    }
}

