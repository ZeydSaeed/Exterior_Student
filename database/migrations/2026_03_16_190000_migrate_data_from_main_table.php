<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('main_table') || ! Schema::hasTable('students')) {
            return;
        }

        $branchIds = $this->mapColumnToId('branches', 'name_ar', 'main_table', 'الفرع', true);
        $majorIds = $this->mapBranchMajorToMajorId();
        $yearIds = $this->mapColumnToId('academic_years', 'year_label', 'main_table', 'العام الدراسي', false);
        $resultIds = $this->mapColumnToId('result_types', 'name_ar', 'main_table', 'النتيجة', true);
        $subjectIds = DB::table('subjects')->pluck('id', 'name_ar')->all();

        $gradeColumns = Config::get('grades_catalog.grade_columns', []);
        $rows = DB::table('main_table')->orderBy('id')->get();

        foreach ($rows as $row) {
            $id = (int) $row->id;
            $examNumber = isset($row->{'الرقم الامتحاني'}) ? trim((string) $row->{'الرقم الامتحاني'}) : '';
            if ($examNumber === '') {
                $examNumber = (string) $id;
            }

            if (DB::table('students')->where('id', $id)->exists()) {
                continue;
            }

            DB::table('students')->insert([
                'id' => $id,
                'exam_number' => $examNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $branchName = isset($row->{'الفرع'}) ? trim((string) $row->{'الفرع'}) : '';
            $majorName = isset($row->{'الاختصاص'}) ? trim((string) $row->{'الاختصاص'}) : '';
            $majorId = $majorIds[$branchName.'|'.$majorName] ?? null;
            if ($majorId === null && $majorName !== '') {
                $majorId = DB::table('majors')->where('name_ar', $majorName)->value('id');
            }

            DB::table('student_personal')->insert([
                'student_id' => $id,
                'first_name' => isset($row->{'اسم الطالب'}) ? trim((string) $row->{'اسم الطالب'}) : '',
                'father_name' => isset($row->{'اسم الاب'}) ? trim((string) $row->{'اسم الاب'}) : '',
                'grandfather_name' => isset($row->{'اسم الجد'}) ? trim((string) $row->{'اسم الجد'}) : '',
                'surname' => isset($row->{'اللقب'}) ? trim((string) $row->{'اللقب'}) : '',
                'gender' => isset($row->{'الجنس'}) ? trim((string) $row->{'الجنس'}) : '',
                'birth_date' => $this->normalizeDate($row->{'التولد'} ?? null),
                'birth_place' => isset($row->{'محل الولادة'}) ? trim((string) $row->{'محل الولادة'}) : '',
                'mother_full_name' => isset($row->{'اسم الام الكامل'}) ? trim((string) $row->{'اسم الام الكامل'}) : '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $branchId = $branchIds[$branchName] ?? null;
            $yearLabel = isset($row->{'العام الدراسي'}) ? trim((string) $row->{'العام الدراسي'}) : '';
            $yearId = $yearIds[$yearLabel] ?? null;
            $resultName = isset($row->{'النتيجة'}) ? trim((string) $row->{'النتيجة'}) : '';
            $resultId = $resultIds[$resultName] ?? null;

            DB::table('student_academic')->insert([
                'student_id' => $id,
                'branch_id' => $branchId,
                'major_id' => $majorId,
                'academic_year_id' => $yearId,
                'result_type_id' => $resultId,
                'total' => (int) round((float) $this->numeric($row->{'المجموع'} ?? $row->{'مجموع'} ?? 0)),
                'average' => $this->numeric($row->{'المعدل'} ?? $row->{'معدل'} ?? 0),
                'round' => isset($row->{'الدور'}) ? trim((string) $row->{'الدور'}) : (isset($row->{'دور'}) ? trim((string) $row->{'دور'}) : ''),
                'last_school' => isset($row->{'اخر مدرسة كان فيها الطالب'}) ? trim((string) $row->{'اخر مدرسة كان فيها الطالب'}) : '',
                'middle_doc_number' => isset($row->{'رقم الوثيقة المتوسطة'}) ? trim((string) $row->{'رقم الوثيقة المتوسطة'}) : '',
                'middle_doc_date' => $this->normalizeDate($row->{'تاريخها'} ?? null),
                'issuing_authority' => isset($row->{'جهة الاصدار'}) ? trim((string) $row->{'جهة الاصدار'}) : '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($gradeColumns as $col) {
                if (! isset($subjectIds[$col])) {
                    continue;
                }
                $value = $row->{$col} ?? null;
                $score = is_numeric($value) ? (int) round((float) $value) : 0;
                DB::table('student_grades')->insert([
                    'student_id' => $id,
                    'subject_id' => $subjectIds[$col],
                    'score' => $score,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $maxId = DB::table('students')->max('id');
        if ($maxId !== null) {
            DB::statement('ALTER TABLE students AUTO_INCREMENT = '.((int) $maxId + 1));
        }
    }

    /** @return array<string, int> */
    private function mapColumnToId(string $table, string $nameCol, string $sourceTable, string $sourceCol, bool $trim): array
    {
        $rows = DB::table($table)->get([$nameCol, 'id']);
        $map = [];
        foreach ($rows as $r) {
            $key = $trim ? trim((string) $r->{$nameCol}) : (string) $r->{$nameCol};
            $map[$key] = (int) $r->id;
        }

        return $map;
    }

    /** @return array<string, int> */
    private function mapBranchMajorToMajorId(): array
    {
        $rows = DB::table('majors')->get(['id', 'name_ar', 'branch_id']);
        $map = [];
        foreach ($rows as $r) {
            $branchName = DB::table('branches')->where('id', $r->branch_id)->value('name_ar');
            $key = (trim((string) $branchName)).'|'.trim((string) $r->name_ar);
            $map[$key] = (int) $r->id;
        }

        return $map;
    }

    private function normalizeDate(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }
        $s = trim((string) $v);
        if ($s === '' || $s === '0000-00-00') {
            return null;
        }

        return $s;
    }

    private function numeric(mixed $v): float
    {
        if (is_numeric($v)) {
            return (float) $v;
        }

        return 0.0;
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_grades')) {
            return;
        }
        DB::table('student_grades')->truncate();
        DB::table('student_academic')->truncate();
        DB::table('student_personal')->truncate();
        DB::table('students')->truncate();
    }
};
