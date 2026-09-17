<?php

use App\Domain\Workstation\WorkstationContext;
use App\Infrastructure\Persistence\CachedSchema;
use App\Infrastructure\Persistence\MySQLCertificateSignatureRepository;
use App\Infrastructure\Persistence\MySQLEmployeeCommandRepository;
use App\Infrastructure\Persistence\MySQLEmployeeQueryRepository;
use App\Infrastructure\Persistence\WorkstationEmployeeCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

function workstationContext(string $id): WorkstationContext
{
    return new class($id) implements WorkstationContext
    {
        public function __construct(private string $id) {}

        public function id(): string
        {
            return $this->id;
        }
    };
}

function workstationCatalog(string $id): WorkstationEmployeeCatalog
{
    return new WorkstationEmployeeCatalog(workstationContext($id));
}

beforeEach(function (): void {
    Schema::dropIfExists('certificate_signatures');
    Schema::dropIfExists('employees');

    Schema::create('employees', function (Blueprint $table): void {
        $table->id();
        $table->string('type');
        $table->string('name');
        $table->unsignedTinyInteger('table_group')->default(1);
        $table->string('workstation_id', 36)->nullable();
    });

    Schema::create('certificate_signatures', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('employee_id')->nullable();
        $table->string('position', 32);
        $table->string('workstation_id', 36)->nullable();
        $table->timestamps();
    });

    CachedSchema::reset();
});

it('keeps organizer and manager selections isolated per workstation', function () {
    $adminId = DB::table('employees')->insertGetId([
        'type' => 'منظم التأييد',
        'name' => 'موظف الأدمن',
        'table_group' => 1,
        'workstation_id' => null,
    ]);
    $clientTemplateId = DB::table('employees')->insertGetId([
        'type' => 'المسؤول',
        'name' => 'موظف القالب',
        'table_group' => 2,
        'workstation_id' => null,
    ]);
    DB::table('certificate_signatures')->insert([
        ['employee_id' => $adminId, 'position' => 'right', 'workstation_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ['employee_id' => $clientTemplateId, 'position' => 'left', 'workstation_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $adminCatalog = workstationCatalog('aaaaaaaa-bbbb-4ccc-8ddd-111111111111');
    $clientCatalog = workstationCatalog('aaaaaaaa-bbbb-4ccc-8ddd-222222222222');

    $adminEmployees = new MySQLEmployeeQueryRepository($adminCatalog);
    $clientEmployees = new MySQLEmployeeQueryRepository($clientCatalog);
    $adminCommands = new MySQLEmployeeCommandRepository($adminCatalog);
    $clientCommands = new MySQLEmployeeCommandRepository($clientCatalog);
    $adminSignatures = new MySQLCertificateSignatureRepository($adminCatalog);
    $clientSignatures = new MySQLCertificateSignatureRepository($clientCatalog);

    $adminList = $adminEmployees->all();
    $clientList = $clientEmployees->all();

    expect($adminList)->toHaveCount(2)
        ->and($clientList)->toHaveCount(2)
        ->and($adminList[0]->id)->not->toBe($clientList[0]->id);

    $adminCommands->update($adminList[0]->id, 'منظم التأييد', 'منظم الأدمن فقط');
    $clientCommands->create('منظم التأييد', 'منظم العميل فقط', 1);

    $adminNames = array_map(static fn ($e) => $e->name, $adminEmployees->all());
    $clientNames = array_map(static fn ($e) => $e->name, $clientEmployees->all());

    expect($adminNames)->toContain('منظم الأدمن فقط')
        ->and($adminNames)->not->toContain('منظم العميل فقط')
        ->and($clientNames)->toContain('منظم العميل فقط')
        ->and($clientNames)->toContain('موظف الأدمن')
        ->and($clientNames)->not->toContain('منظم الأدمن فقط');

    $adminNewOrganizer = collect($adminEmployees->all())->firstWhere('name', 'منظم الأدمن فقط');
    $clientNewOrganizer = collect($clientEmployees->all())->firstWhere('name', 'منظم العميل فقط');

    $adminSignatures->setSignature('right', $adminNewOrganizer->id);
    $clientSignatures->setSignature('right', $clientNewOrganizer->id);

    expect($adminSignatures->getEmployeeIdByPosition('right'))->toBe($adminNewOrganizer->id)
        ->and($clientSignatures->getEmployeeIdByPosition('right'))->toBe($clientNewOrganizer->id)
        ->and($adminSignatures->getEmployeeIdByPosition('right'))->not->toBe($clientSignatures->getEmployeeIdByPosition('right'));
});
