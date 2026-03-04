<?php

namespace App\Domain\Employee;

/**
 * نوع الموظف (Value Object للقيم الثابتة).
 */
final class EmployeeType
{
    public const ORGANIZER = 'organizer';
    public const MANAGER = 'manager';

    /**
     * إرجاع جميع الأنواع المسموح بها.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ORGANIZER,
            self::MANAGER,
        ];
    }
}

