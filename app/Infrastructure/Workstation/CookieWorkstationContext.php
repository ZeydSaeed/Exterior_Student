<?php

namespace App\Infrastructure\Workstation;

use App\Domain\Workstation\WorkstationContext;
use App\Http\Middleware\AssignWorkstationId;
use Illuminate\Http\Request;

/**
 * يقرأ معرّف الحاسبة من الطلب الحالي (كوكي/سمة) دون أي إعداد شبكة.
 */
final class CookieWorkstationContext implements WorkstationContext
{
    public function __construct(
        private Request $request
    ) {}

    public function id(): string
    {
        $fromAttribute = (string) $this->request->attributes->get(AssignWorkstationId::COOKIE, '');
        if (AssignWorkstationId::isValid($fromAttribute)) {
            return strtolower($fromAttribute);
        }

        $fromCookie = (string) $this->request->cookies->get(AssignWorkstationId::COOKIE, '');
        if (AssignWorkstationId::isValid($fromCookie)) {
            return strtolower($fromCookie);
        }

        return 'console';
    }
}
