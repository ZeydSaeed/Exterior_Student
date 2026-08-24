<?php

use App\Application\Student\Service\AllBlockedGradesResultResolver;
use App\Domain\Student\SubjectCatalogInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تحديث النتيجة إلى «حجب» لكل طالب درجات جميع مواده «حجب».
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('students')
            || ! Schema::hasTable('student_academic')
            || ! Schema::hasTable('student_grades')
            || ! Schema::hasTable('result_types')) {
            return;
        }

        $hajbResultId = DB::table('result_types')->where('name_ar', 'حجب')->value('id');
        if ($hajbResultId === null) {
            $now = now();
            $hajbResultId = DB::table('result_types')->insertGetId([
                'name_ar' => 'حجب',
                'sort_order' => ((int) DB::table('result_types')->max('sort_order')) + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $hajbResultId = (int) $hajbResultId;

        /** @var SubjectCatalogInterface $catalog */
        $catalog = app(SubjectCatalogInterface::class);
        /** @var AllBlockedGradesResultResolver $resolver */
        $resolver = app(AllBlockedGradesResultResolver::class);

        $usesMajorSubject = Schema::hasColumn('student_grades', 'major_subject_id');

        DB::table('students as s')
            ->join('student_academic as a', 'a.student_id', '=', 's.id')
            ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
            ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
            ->select([
                's.id as student_id',
                'a.academic_year_id',
                'a.major_id',
                'b.name_ar as branch',
                'm.name_ar as major',
            ])
            ->orderBy('s.id')
            ->chunkById(200, function ($rows) use ($catalog, $resolver, $hajbResultId, $usesMajorSubject): void {
                foreach ($rows as $row) {
                    $studentId = (int) $row->student_id;
                    $branch = trim((string) ($row->branch ?? ''));
                    $major = trim((string) ($row->major ?? ''));
                    $subjects = $catalog->getSubjectsFor($branch, $major);
                    if ($subjects === []) {
                        continue;
                    }

                    $grades = $this->loadGradesForStudent(
                        $studentId,
                        (int) ($row->academic_year_id ?? 0),
                        (int) ($row->major_id ?? 0),
                        $usesMajorSubject,
                    );

                    if (! $resolver->shouldForceBlockedResult($branch, $major, $grades)) {
                        continue;
                    }

                    DB::table('student_academic')
                        ->where('student_id', $studentId)
                        ->update([
                            'result_type_id' => $hajbResultId,
                            'updated_at' => now(),
                        ]);
                }
            }, 's.id', 'student_id');
    }

    public function down(): void
    {
        // لا تراجع تلقائي: النتيجة قد تكون حُدّثت يدوياً بعد الترحيل.
    }

    /**
     * @return list<array{subject: string, score: string}>
     */
    private function loadGradesForStudent(int $studentId, int $academicYearId, int $majorId, bool $usesMajorSubject): array
    {
        if ($usesMajorSubject) {
            if ($academicYearId < 1 || $majorId < 1) {
                return [];
            }

            $rows = DB::table('student_grades as g')
                ->join('major_subjects as ms', 'ms.id', '=', 'g.major_subject_id')
                ->join('subjects as sub', 'sub.id', '=', 'ms.subject_id')
                ->where('g.student_id', $studentId)
                ->where('g.academic_year_id', $academicYearId)
                ->where('ms.major_id', $majorId)
                ->select(['sub.name_ar as subject', 'g.score'])
                ->get();
        } else {
            $rows = DB::table('student_grades as g')
                ->join('subjects as sub', 'sub.id', '=', 'g.subject_id')
                ->where('g.student_id', $studentId)
                ->select(['sub.name_ar as subject', 'g.score'])
                ->get();
        }

        $grades = [];
        foreach ($rows as $row) {
            $grades[] = [
                'subject' => (string) ($row->subject ?? ''),
                'score' => trim((string) ($row->score ?? '')),
            ];
        }

        return $grades;
    }
};
