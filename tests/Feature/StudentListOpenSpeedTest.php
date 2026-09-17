<?php

use App\Infrastructure\Persistence\StudentListQueryCache;
use Illuminate\Support\Facades\Cache;

it('opens the students list without a session-normalize redirect', function () {
    $source = (string) file_get_contents(app_path('Http/Controllers/StudentController.php'));

    expect($source)->toContain('currentPageResolver')
        ->and($source)->not->toContain('shouldRedirectToNormalize')
        ->and($source)->not->toContain('redirect()->to(StudentListFiltersSession::indexUrl');
});

it('paginates the students list from a cached count instead of recounting every open', function () {
    $source = (string) file_get_contents(app_path('Infrastructure/Persistence/MySQLStudentQueryRepository.php'));
    $command = (string) file_get_contents(app_path('Infrastructure/Persistence/MySQLStudentCommandRepository.php'));

    expect($source)->toContain('function paginateFilteredList')
        ->and($source)->toContain('StudentListQueryCache::rememberCount')
        ->and($source)->toContain('StudentListQueryCache::rememberPage')
        ->and($source)->toContain('countWithFilters')
        ->and($source)->not->toContain('->paginate(self::PER_PAGE)')
        ->and($command)->toContain('forgetStudentListCaches')
        ->and($command)->toContain('StudentListQueryCache::bump()');
});

it('prerenders بيانات الطلبة as soon as the dashboard loads', function () {
    $layout = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $prefetch = (string) file_get_contents(public_path('js/nav-prefetch.js'));
    $css = (string) file_get_contents(public_path('css/dashboard.css'));

    expect($layout)->toContain('type="speculationrules"')
        ->and($layout)->toContain("'eagerness' => 'immediate'")
        ->and($layout)->toContain('cairo:400,600,700,800')
        ->and($layout)->toContain('css/dashboard.css')
        ->and($prefetch)->toContain('warmupStudentsLink')
        ->and($prefetch)->toContain('a[aria-label="بيانات الطلبة"]')
        ->and($prefetch)->toContain("link.rel = 'prefetch'")
        ->and($prefetch)->toContain("document.addEventListener('pointerdown'")
        ->and($prefetch)->not->toContain('style.')
        ->and($css)->toContain("--dashboard-filter-font: 'Cairo', Tahoma, sans-serif")
        ->and($css)->toContain('width: 1.75cm');
});

it('reuses a cached student list count until writes bump the version', function () {
    Cache::flush();
    $calls = 0;
    $filters = ['year' => '2025-2026', 'branch' => 'الصناعي'];

    $first = StudentListQueryCache::rememberCount($filters, function () use (&$calls): int {
        $calls++;

        return 40;
    });
    $second = StudentListQueryCache::rememberCount($filters, function () use (&$calls): int {
        $calls++;

        return 99;
    });

    expect($first)->toBe(40)
        ->and($second)->toBe(40)
        ->and($calls)->toBe(1);

    StudentListQueryCache::bump();

    $third = StudentListQueryCache::rememberCount($filters, function () use (&$calls): int {
        $calls++;

        return 41;
    });

    expect($third)->toBe(41)
        ->and($calls)->toBe(2);

    $pageCalls = 0;
    $firstPage = StudentListQueryCache::rememberPage($filters, 1, function () use (&$pageCalls): array {
        $pageCalls++;

        return [['id' => 7]];
    });
    $secondPage = StudentListQueryCache::rememberPage($filters, 1, function () use (&$pageCalls): array {
        $pageCalls++;

        return [['id' => 8]];
    });

    expect($firstPage)->toBe([['id' => 7]])
        ->and($secondPage)->toBe([['id' => 7]])
        ->and($pageCalls)->toBe(1);
});
