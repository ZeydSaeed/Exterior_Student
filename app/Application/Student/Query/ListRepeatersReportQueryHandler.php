<?php

namespace App\Application\Student\Query;

use App\Application\Student\DTO\ListRepeatersReportResponseDTO;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

final class ListRepeatersReportQueryHandler
{
    private const FAIL_THRESHOLD = 50;

    public function __construct(
        private StudentQueryRepository $repository,
        private SubjectCatalogInterface $subjectCatalog,
    ) {}

    public function handle(ListRepeatersReportQuery $query): ListRepeatersReportResponseDTO
    {
        $data = $this->repository->listRepeatersReport($query->filtersForRepository());
        $groups = $this->enrichGroupsWithSubjectStats($data['groups'] ?? []);

        return new ListRepeatersReportResponseDTO(
            groups: $groups,
            totalRepeaters: (int) ($data['stats']['total_repeaters'] ?? 0),
            academicYears: $data['filters']['academicYears'],
            branches: $data['filters']['branches'],
            majors: $data['filters']['majors'],
            genders: $data['filters']['genders'],
            selectedYear: $query->year !== null && trim($query->year) !== '' ? trim($query->year) : null,
        );
    }

    /**
     * @param  list<array{branch:string,major:string,students:list<array{subjects:list<array{subject:string,score:string}>}>}>  $groups
     * @return list<array{branch:string,major:string,students:list<array{subjects:list<array{subject:string,score:string}>}>,subject_columns:list<string>,subject_repeater_counts:array<string,int>}>
     */
    private function enrichGroupsWithSubjectStats(array $groups): array
    {
        $out = [];
        foreach ($groups as $group) {
            $branch = trim((string) ($group['branch'] ?? ''));
            $major = trim((string) ($group['major'] ?? ''));
            $catalogSubjects = $this->subjectCatalog->getSubjectsFor($branch, $major);
            $subjectColumns = $this->resolveSubjectColumns($catalogSubjects, $group['students'] ?? []);
            $counts = array_fill_keys($subjectColumns, 0);

            foreach ($group['students'] ?? [] as $student) {
                foreach ($student['subjects'] ?? [] as $row) {
                    $subject = trim((string) ($row['subject'] ?? ''));
                    $score = trim((string) ($row['score'] ?? ''));
                    if ($subject === '' || ! isset($counts[$subject])) {
                        continue;
                    }
                    if ($score !== '' && is_numeric($score) && (float) $score < self::FAIL_THRESHOLD) {
                        $counts[$subject]++;
                    }
                }
            }

            $subjectColumnsWithRepeaters = [];
            $countsWithRepeaters = [];
            foreach ($subjectColumns as $subjectName) {
                $count = (int) ($counts[$subjectName] ?? 0);
                if ($count <= 0) {
                    continue;
                }
                $subjectColumnsWithRepeaters[] = $subjectName;
                $countsWithRepeaters[$subjectName] = $count;
            }

            $group['subject_columns'] = $subjectColumnsWithRepeaters;
            $group['subject_repeater_counts'] = $countsWithRepeaters;
            $out[] = $group;
        }

        return $out;
    }

    /**
     * @param  list<string>  $catalogSubjects
     * @param  list<array{subjects?: list<array{subject?: string, score?: string}>}>  $students
     * @return list<string>
     */
    private function resolveSubjectColumns(array $catalogSubjects, array $students): array
    {
        $columns = [];
        foreach ($catalogSubjects as $subject) {
            $name = trim((string) $subject);
            if ($name !== '' && ! in_array($name, $columns, true)) {
                $columns[] = $name;
            }
        }

        foreach ($students as $student) {
            foreach ($student['subjects'] ?? [] as $row) {
                $name = trim((string) ($row['subject'] ?? ''));
                if ($name !== '' && ! in_array($name, $columns, true)) {
                    $columns[] = $name;
                }
            }
        }

        return $columns;
    }
}
