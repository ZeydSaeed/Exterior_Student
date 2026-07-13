<?php

namespace App\Http\Controllers;

use App\Application\Student\DTO\StudentDocumentPageDTO;
use App\Application\Student\Query\GetBulkStudentDocumentsPrintQueryHandler;
use App\Application\Student\Query\ListStudentsQuery;
use App\Application\Student\Query\ListStudentsQueryHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * صفحة القيود — قيد فارغ عند الفتح، ومعاينة/طباعة حسب فلاتر الصفحة.
 */
final class StudentDocumentsBulkPrintController extends Controller
{
    /** @var list<string> */
    private const FILTER_KEYS = ['branch', 'major', 'gender', 'year', 'round', 'result'];

    public function __construct(
        private GetBulkStudentDocumentsPrintQueryHandler $handler,
        private ListStudentsQueryHandler $listHandler,
    ) {}

    public function __invoke(Request $request): View
    {
        $normalized = $this->filtersFromRequest($request);
        $hasActiveFilters = $this->hasActiveFilters($normalized);

        $query = ListStudentsQuery::fromArray(array_merge(
            [
                'branch' => null,
                'major' => null,
                'gender' => null,
                'year' => null,
                'round' => null,
                'result' => null,
                'search' => null,
            ],
            $normalized
        ));

        if (! $hasActiveFilters) {
            $filterOnlyQuery = ListStudentsQuery::fromArray([
                'branch' => null,
                'major' => null,
                'gender' => null,
                'year' => null,
                'round' => null,
                'result' => null,
                'search' => null,
            ]);
            $listResponse = $this->listHandler->handle($filterOnlyQuery);

            return view('students.documents-bulk-print', [
                'branches' => $listResponse->branches,
                'majors' => $listResponse->majors,
                'genders' => $listResponse->genders,
                'academicYears' => $listResponse->academicYears,
                'roundOptions' => $listResponse->roundOptions,
                'resultOptions' => $listResponse->resultOptions,
                'useStudentListSessionMerge' => false,
                'studentIds' => [],
                'initialDtosById' => [],
                'showBlankDocument' => true,
                'blankDto' => StudentDocumentPageDTO::blank(),
            ]);
        }

        $listResponse = $this->listHandler->handle($query);

        $studentIds = $this->handler->listIds($query);
        $focusId = $request->query('focus_id');
        $focusIdInt = is_numeric($focusId) ? (int) $focusId : null;
        $window = GetBulkStudentDocumentsPrintQueryHandler::initialWindow($studentIds, $focusIdInt);
        $initialDtos = $this->handler->handleForIds($window['ids']);

        $initialDtosById = [];
        foreach ($initialDtos as $dto) {
            $initialDtosById[$dto->studentId] = $dto;
        }

        return view('students.documents-bulk-print', [
            'branches' => $listResponse->branches,
            'majors' => $listResponse->majors,
            'genders' => $listResponse->genders,
            'academicYears' => $listResponse->academicYears,
            'roundOptions' => $listResponse->roundOptions,
            'resultOptions' => $listResponse->resultOptions,
            'useStudentListSessionMerge' => false,
            'studentIds' => $studentIds,
            'initialDtosById' => $initialDtosById,
            'showBlankDocument' => false,
            'blankDto' => null,
        ]);
    }

    public function chunk(Request $request): View|Response
    {
        $normalized = $this->filtersFromRequest($request);

        if (! $this->hasActiveFilters($normalized)) {
            return response('', 204);
        }

        $query = ListStudentsQuery::fromArray(array_merge(
            [
                'branch' => null,
                'major' => null,
                'gender' => null,
                'year' => null,
                'round' => null,
                'result' => null,
                'search' => null,
            ],
            $normalized
        ));

        $allowedIds = $this->handler->listIds($query);
        $allowedSet = array_flip($allowedIds);

        $rawIds = $request->query('ids', '');
        $requestedIds = is_array($rawIds)
            ? array_map('intval', $rawIds)
            : array_values(array_filter(array_map('intval', explode(',', (string) $rawIds))));

        $ids = [];
        foreach ($requestedIds as $id) {
            if (isset($allowedSet[$id])) {
                $ids[] = $id;
            }
        }

        if ($ids === []) {
            return response('', 204);
        }

        $dtos = $this->handler->handleForIds($ids);

        return view('students.partials.documents-bulk-chunk', ['dtos' => $dtos]);
    }

    /**
     * @return array<string, string|null>
     */
    private function filtersFromRequest(Request $request): array
    {
        $out = [];
        foreach (self::FILTER_KEYS as $key) {
            if (! $request->query->has($key)) {
                continue;
            }
            $value = trim((string) $request->query->get($key));
            $out[$key] = $value !== '' ? $value : null;
        }

        return $out;
    }

    /**
     * @param  array<string, string|null>  $normalized
     */
    private function hasActiveFilters(array $normalized): bool
    {
        foreach (self::FILTER_KEYS as $key) {
            if (! empty($normalized[$key])) {
                return true;
            }
        }

        return false;
    }
}
