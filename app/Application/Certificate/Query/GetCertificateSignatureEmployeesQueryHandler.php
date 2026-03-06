<?php

namespace App\Application\Certificate\Query;

use App\Domain\Certificate\CertificateSignatureRepository;
use App\Domain\Employee\EmployeeQueryRepository;

/**
 * استعلام جلب بيانات الموظفين المعيّنين للتوقيع (لصفحة التأييد).
 *
 * @return list<array{type: string, name: string}> [الموظف اليمين، الموظف اليسار]
 */
final class GetCertificateSignatureEmployeesQueryHandler
{
    public function __construct(
        private CertificateSignatureRepository $signatureRepository,
        private EmployeeQueryRepository $employeeRepository
    ) {}

    /**
     * @return list<array{type: string, name: string}>
     */
    public function handle(): array
    {
        $rightId = $this->signatureRepository->getEmployeeIdByPosition('right');
        $leftId = $this->signatureRepository->getEmployeeIdByPosition('left');

        $all = $this->employeeRepository->all();
        $byId = [];
        foreach ($all as $emp) {
            $byId[$emp->id] = ['type' => $emp->type, 'name' => $emp->name];
        }

        return [
            $rightId !== null && isset($byId[$rightId]) ? $byId[$rightId] : ['type' => 'منظم التأييد', 'name' => 'غير محدد'],
            $leftId !== null && isset($byId[$leftId]) ? $byId[$leftId] : ['type' => 'مسؤول شعبة شؤون الطلبة', 'name' => 'غير محدد'],
        ];
    }
}
