<?php

namespace App\Domain\Employee;

use DomainException;

/**
 * كيان الموظف في طبقة الـ Domain.
 */
final class Employee
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $name,
        public readonly int $tableGroup = 1,
    ) {}

    /**
     * إنشاء موظف جديد مع قواعد الدومين الأساسية.
     */
    public static function create(string $type, string $name): self
    {
        $name = trim($name);
        if ($name === '') {
            throw new DomainException('Employee name is required');
        }

        $type = trim($type);
        if ($type === '') {
            throw new DomainException('Employee type is required');
        }

        // في حالة الإنشاء الجديد، يمكن أن يكون id = 0 (غير محفوظ بعد)
        return new self(0, $type, $name, 1);
    }

    public static function createWithTableGroup(string $type, string $name, int $tableGroup): self
    {
        $name = trim($name);
        if ($name === '') {
            throw new DomainException('Employee name is required');
        }
        $type = trim($type);
        if ($type === '') {
            throw new DomainException('Employee type is required');
        }
        if ($tableGroup !== 1 && $tableGroup !== 2) {
            throw new DomainException('Table group must be 1 or 2');
        }
        return new self(0, $type, $name, $tableGroup);
    }

    public function withName(string $name): self
    {
        $name = trim($name);
        if ($name === '') {
            throw new DomainException('Employee name is required');
        }

        return new self($this->id, $this->type, $name, $this->tableGroup);
    }

    public function withType(string $type): self
    {
        $type = trim($type);
        if ($type === '') {
            throw new DomainException('Employee type is required');
        }

        return new self($this->id, $type, $this->name, $this->tableGroup);
    }
}

