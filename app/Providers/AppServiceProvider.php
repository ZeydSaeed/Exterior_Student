<?php

namespace App\Providers;

use App\Domain\Employee\EmployeeCommandRepository;
use App\Domain\Employee\EmployeeQueryRepository;
use App\Domain\Certificate\CertificateSignatureRepository;
use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\StudentReadRepository;
use App\Domain\Student\SubjectCatalogInterface;
use App\Infrastructure\Grades\ConfigSubjectCatalog;
use App\Infrastructure\Persistence\MySQLCertificateSignatureRepository;
use App\Infrastructure\Persistence\MySQLEmployeeCommandRepository;
use App\Infrastructure\Persistence\MySQLEmployeeQueryRepository;
use App\Infrastructure\Persistence\MySQLStudentCommandRepository;
use App\Infrastructure\Persistence\MySQLStudentQueryRepository;
use App\Infrastructure\Persistence\MySQLStudentReadRepository;
use App\Support\Highlight;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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

        // Employees
        $this->app->bind(EmployeeQueryRepository::class, MySQLEmployeeQueryRepository::class);
        $this->app->bind(EmployeeCommandRepository::class, MySQLEmployeeCommandRepository::class);

        // Certificate signatures
        $this->app->bind(CertificateSignatureRepository::class, MySQLCertificateSignatureRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        Blade::directive('highlight', function (string $expression) {
            return "<?php echo \App\Support\Highlight::render(" . $expression . "); ?>";
        });
    }
}
