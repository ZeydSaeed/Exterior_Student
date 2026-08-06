<?php

namespace App\Providers;

use App\Domain\Attestation\AttestationCommandRepository;
use App\Domain\Attestation\AttestationQueryRepository;
use App\Domain\Backup\Repositories\DatabaseBackupRepository;
use App\Domain\Certificate\CertificateSignatureRepository;
use App\Domain\Employee\EmployeeCommandRepository;
use App\Domain\Employee\EmployeeQueryRepository;
use App\Domain\Record\RecordCommandRepository;
use App\Domain\Record\RecordQueryRepository;
use App\Domain\Student\BranchMajorCatalogInterface;
use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentImportTempRepository;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\StudentReadRepository;
use App\Domain\Student\StudentResultsImportTempRepository;
use App\Domain\Student\SubjectCatalogInterface;
use App\Infrastructure\Grades\ConfigSubjectCatalog;
use App\Infrastructure\Persistence\MySQLAttestationCommandRepository;
use App\Infrastructure\Persistence\MySQLAttestationQueryRepository;
use App\Infrastructure\Persistence\MySQLBranchMajorCatalog;
use App\Infrastructure\Persistence\MySQLCertificateSignatureRepository;
use App\Infrastructure\Persistence\MySQLDatabaseBackupRepository;
use App\Infrastructure\Persistence\MySQLEmployeeCommandRepository;
use App\Infrastructure\Persistence\MySQLEmployeeQueryRepository;
use App\Infrastructure\Persistence\MySQLRecordCommandRepository;
use App\Infrastructure\Persistence\MySQLRecordQueryRepository;
use App\Infrastructure\Persistence\MySQLStudentCommandRepository;
use App\Infrastructure\Persistence\MySQLStudentImportTempRepository;
use App\Infrastructure\Persistence\MySQLStudentQueryRepository;
use App\Infrastructure\Persistence\MySQLStudentReadRepository;
use App\Infrastructure\Persistence\MySQLStudentResultsImportTempRepository;
use App\Support\StudentListFiltersSession;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Students
        $this->app->bind(StudentQueryRepository::class, MySQLStudentQueryRepository::class);
        $this->app->bind(StudentReadRepository::class, MySQLStudentReadRepository::class);
        $this->app->bind(StudentCommandRepository::class, MySQLStudentCommandRepository::class);
        $this->app->bind(SubjectCatalogInterface::class, ConfigSubjectCatalog::class);
        $this->app->bind(StudentImportTempRepository::class, MySQLStudentImportTempRepository::class);
        $this->app->bind(StudentResultsImportTempRepository::class, MySQLStudentResultsImportTempRepository::class);
        $this->app->bind(BranchMajorCatalogInterface::class, MySQLBranchMajorCatalog::class);

        // Employees
        $this->app->bind(EmployeeQueryRepository::class, MySQLEmployeeQueryRepository::class);
        $this->app->bind(EmployeeCommandRepository::class, MySQLEmployeeCommandRepository::class);

        // Certificate signatures
        $this->app->bind(CertificateSignatureRepository::class, MySQLCertificateSignatureRepository::class);

        // Attestations (certificate table)
        $this->app->bind(AttestationQueryRepository::class, MySQLAttestationQueryRepository::class);
        $this->app->bind(AttestationCommandRepository::class, MySQLAttestationCommandRepository::class);

        // Database backup
        $this->app->bind(DatabaseBackupRepository::class, MySQLDatabaseBackupRepository::class);

        // Student records (documents)
        $this->app->bind(RecordQueryRepository::class, MySQLRecordQueryRepository::class);
        $this->app->bind(RecordCommandRepository::class, MySQLRecordCommandRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.debug') && class_exists(DB::class)) {
            DB::listen(function ($query): void {
                $time = $query->time;
                if ($time > 1000) {
                    Log::warning('Slow query', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms' => $time,
                    ]);
                }
            });
        }

        Paginator::useBootstrapFour();

        View::composer('layouts.dashboard', function ($view) {
            $view->with('students_index_url', StudentListFiltersSession::indexUrl(request()));
            $view->with('students_statistics_url', StudentListFiltersSession::statisticsUrl(request()));
            $view->with('students_bulk_print_url', route('students.documents.bulk-print'));
        });

        Blade::directive('highlight', function (string $expression) {
            return "<?php echo \App\Support\Highlight::render(".$expression.'); ?>';
        });
    }
}
