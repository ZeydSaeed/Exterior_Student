<?php

namespace App\Domain\Workstation;

/**
 * معرّف الحاسبة الحالية داخل الشبكة المحلية (لا علاقة له بإعدادات LAN).
 */
interface WorkstationContext
{
    public function id(): string;
}
